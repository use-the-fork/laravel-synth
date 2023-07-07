<?php

declare(strict_types=1);

namespace Blinq\Synth\Services;

use Blinq\Synth\Commands\SynthCommand;
use Blinq\Synth\Exceptions\MissingOpenAIKeyException;
use Blinq\Synth\Helpers\ChatTokenService;
use Blinq\Synth\Interfaces\PromptInterface;
use Blinq\Synth\MainMenu;
use Blinq\Synth\Modules;
use Blinq\Synth\ValueObjects\AttachedFileValueObject;
use Blinq\Synth\ValueObjects\ChatMessageValueObject;
use Blinq\Synth\ValueObjects\ExtraValueObject;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Arr;
use OpenAI;
use OpenAI\Client;

class SynthService
{
    public $cmd;

    public ChatMessageValueObject $systemMessage;

    public Client $openaiClient;

    private array $messageToSend;

    private array $currentMessageToSend;

    private array $finalResponse;

    /**
     * @param  ChatMessageValueObject[]  $chatHistory
     * @param  AttachedFileValueObject[]  $attachedFiles
     */
    public function __construct(
        public MainMenu $mainMenu,
        public Modules $modules,
        public array $functions = [],
        public array $chatHistory = [],
        public array $attachedFiles = [],
        public array $attachedExtras = [],
    ) {

        if ( ! config('synth.openai_key')) {
            throw MissingOpenAIKeyException::make();
        }

        $this->openaiClient = OpenAI::factory()
            ->withApiKey(config('synth.openai_key'))
            ->withHttpClient($client = new GuzzleClient(['timeout' => 30.0])) // default: HTTP client found using PSR-18 HTTP Client Discovery
            ->make();

    }

    public function getSessionInformation(): void
    {
        if ( ! empty($this->systemMessage)) {
            $this->prepareChatMessage();
            $getModalToUse = ChatTokenService::getModalToUse($this->messageToSend);
            $this->cmd->info('Currently using ' . $getModalToUse['model'] . ' with ' . $getModalToUse['estimatedCount'] . ' tokens (' . $getModalToUse['percent_used'] . '% used)');
        }

        $fileCount = [
            'total' => 0,
            'modified' => 0,
        ];
        foreach ($this->attachedFiles as $file) {
            $fileCount['total']++;
            if ($file->isModified()) {
                $fileCount['modified']++;
            }
        }
        $this->cmd->info('Total files ' . $fileCount['total'] . ' with ' . $fileCount['modified'] . ' modified');
    }

    public function chat(string $currentQuestion): void
    {

        $cmd = $this->cmd->ask($currentQuestion);
        $this->prepareChatMessage();

        $command = '';
        while ('commit' != $command) {
            $currentMessageToSend = [...$this->messageToSend, ChatMessageValueObject::make('user', $cmd)];

            $this->streamConversation($currentMessageToSend);

            $this->cmd->newLine(2);
            $this->getCurrentChatInformation($currentMessageToSend);

            $this->cmd->comment('Edit: (space then press Tab), Add message and continue conversation: (add), Accept Response: (commit), Discard and go to main menu: (exit)');
            $command = $this->cmd->anticipate('Accept Response?', fn (string $input) => [' ' . $cmd, 'commit']);

            if ('exit' == $command) {
                return;
            } elseif ('add' == $command) {
                $this->addUserAssistantMessage($cmd);
                $cmd = $this->cmd->ask('User: ');
            } else {
                $cmd = $command;
            }

        }

        $this->cmd->info('Committing Response');
        $this->updateCurrentMessageToSend($cmd);

        $this->streamConversation($this->currentMessageToSend, true);

        if ('function_call' == $this->finalResponse['finish_reason']) {
            $this->executeFunctionCall();
        }
    }

    public function setSynthCommand(SynthCommand $cmd): void
    {
        $this->cmd = $cmd;

        //Set Chat controller for all classes
        $this->mainMenu->setSynthController();
        $this->modules->setSynthController();
    }

    public function setPromptInterface(PromptInterface $promptInterface): void
    {
        $this->systemMessage = $promptInterface->getSystem();
    }

    public function getChatHistory(): array
    {
        return $this->chatHistory;
    }

    public function addToChatHistory(string $role, string $content): void
    {
        $this->chatHistory[] = ChatMessageValueObject::make(
            role: $role,
            content: $content,
        );
    }

    //commands specific to adding and removing attachments

    public function getAttachedFiles(): array
    {
        return $this->attachedFiles;
    }

    public function addAttachedFile($key, $value, $modified = false): void
    {
        $base = basename($key);
        $this->cmd->comment("Attaching {$base}");
        $this->attachedFiles[$key] = AttachedFileValueObject::make($key, $value, $modified);
    }

    public function removeAttachedFile($key): void
    {
        $base = basename($key);
        $this->cmd->comment("Removed {$base}");
        unset($this->attachedFiles[$key]);
    }

    public function clearAttachedFiles(): void
    {
        $this->attachedFiles = [];

        $this->cmd->comment('Attachments cleared');
        $this->cmd->newLine();
    }

    public function setAttachedFiles($attachedFiles = []): array
    {
        return $this->attachedFiles = $attachedFiles;
    }

    //commands specific to adding and removing attachments

    public function getExtras(): array
    {
        return $this->attachedExtras;
    }

    public function addExtra($key, $value): void
    {
        $this->cmd->comment("Attaching {$key}");
        $this->attachedFiles[] = ExtraValueObject::make($key, $value);
    }

    public function removeExtra($key): void
    {
        $this->cmd->comment("Removed {$key}");
        unset($this->attachedFiles[$key]);
    }

    public function clearExtra(): void
    {
        $this->attachedFiles = [];

        $this->cmd->comment('Attachments cleared');
        $this->cmd->newLine();
    }

    private function getCurrentChatInformation(array $messages): void
    {
        $getModalToUse = ChatTokenService::getModalToUse($messages);
        $this->cmd->info('Currently using ' . $getModalToUse['model'] . ' with ' . $getModalToUse['estimatedCount'] . ' tokens (' . $getModalToUse['percent_used'] . '% used)');
    }

    private function prepareChatMessage(): void
    {
        $this->finalResponse = [
            'role' => '',
            'content' => str(''),
            'function_call' => [
                'name' => str(''),
                'arguments' => str(''),
            ],
            'finish_reason' => '',
        ];

        $this->messageToSend = [];

        $this->messageToSend[] = ChatMessageValueObject::make($this->systemMessage->getRole(), $this->addGlobalInstructions($this->systemMessage->getContent()));

        if ( ! empty($this->attachedFiles)) {
            $filesToSend = "# Anythings below this line is what we have currently built. Files are seperated by \"\"\"\n\n";
            foreach ($this->attachedFiles as $file) {
                $filesToSend .= $file->getMinifiedContent() . '"""' . "\n";
            }
            $this->messageToSend[] = ChatMessageValueObject::make('user', $filesToSend);
        }
    }

    private function addGlobalInstructions(string $message): string
    {
        $message = [
            $message,
            '* If you need more information about a third party class or method the application is using respond with the need_documentation function.',
            '* If you need more information about a class being used in the application respond with the need_class function.',
            ...config('synth.global_instructions'),
        ];

        return implode("\n", $message);
    }

    private function streamConversation(array $currentMessageToSend, bool $withFunctions = false): void
    {
        $getModalToUse = ChatTokenService::getModalToUse($currentMessageToSend);

        $streamMessage = [
            'model' => $getModalToUse['model'],
            'messages' => $currentMessageToSend,
        ];

        if ( ! empty($withFunctions)) {

            $functions = [];
            foreach ($this->functions as $function) {
                $functions[] = $function->getFunctionJson();
            }

            $streamMessage['functions'] = $functions;
        }

        $stream = $this->openaiClient->chat()->createStreamed($streamMessage);

        $this->processStreamedResponses($stream);
    }

    private function processStreamedResponses($stream): void
    {
        foreach ($stream as $response) {
            $response = $response->choices[0]->toArray();

            $this->updateFinalResponse($response);

            $content = Arr::get($response, 'delta.content', '');
            $function_call = Arr::get($response, 'delta.function_call.name', '');
            $arguments = Arr::get($response, 'delta.function_call.arguments', '');

            $this->finalResponse['content'] = $this->finalResponse['content']->append($content);
            $this->finalResponse['function_call']['name'] = $this->finalResponse['function_call']['name']->append($function_call);
            $this->finalResponse['function_call']['arguments'] = $this->finalResponse['function_call']['arguments']->append($arguments);

            $this->cmd->getOutput()->write($content . $function_call . $arguments);
        }
    }

    private function updateFinalResponse(array $response): void
    {
        if (Arr::get($response, 'delta.role')) {
            $this->finalResponse['role'] = Arr::get($response, 'delta.role');
        }

        if (Arr::get($response, 'finish_reason')) {
            $this->finalResponse['finish_reason'] = Arr::get($response, 'finish_reason');
        }
    }

    private function addUserAssistantMessage(string $cmd): void
    {
        $this->messageToSend[] = ChatMessageValueObject::make('user', $cmd);
        $this->messageToSend[] = ChatMessageValueObject::make('assistant', (string) $this->finalResponse['content']);
    }

    private function updateCurrentMessageToSend(string $cmd): void
    {
        $this->currentMessageToSend = $this->messageToSend;

        $this->currentMessageToSend[] = ChatMessageValueObject::make('assistant', (string) $this->finalResponse['content']);
        $this->currentMessageToSend[] = ChatMessageValueObject::make('user', "Instructions:\n1. Make the changes as discussed above using the save_files function.\n 2.Respond only with the function call.\n 3. respond with the WHOLE file. \n 4. Do not truncate any code.");
    }

    private function executeFunctionCall(): void
    {
        $functionName = (string) $this->finalResponse['function_call']['name'];
        if ( ! empty($this->functions[$functionName])) {
            $functionResult = $this->functions[$functionName]->doFunction((string) $this->finalResponse['function_call']['arguments'], $this->getAttachedFiles());
            $this->setAttachedFiles($functionResult);
        }

        //ToDo: Add error handling
    }
}
