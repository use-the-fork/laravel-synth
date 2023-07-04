<?php

namespace Blinq\Synth\Controllers;

use Blinq\Synth\Commands\SynthCommand;
use Blinq\Synth\Exceptions\MissingOpenAIKeyException;
use Blinq\Synth\Helpers\TokenService;
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

class SynthController
{
    public $cmd;

    public ChatMessageValueObject $systemMessage;

    private Client $openaiClient;

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

        if (! config('synth.openai_key')) {
            throw MissingOpenAIKeyException::make();
        }

        $this->openaiClient = OpenAI::factory()
            ->withApiKey(config('synth.openai_key'))
            ->withHttpClient($client = new GuzzleClient(['timeout' => 30.0])) // default: HTTP client found using PSR-18 HTTP Client Discovery
            ->make();

    }

    public function getSessionInformation(): void
    {
        if (! empty($this->systemMessage)) {
            $this->prepareChatMessage();
            $getModalToUse = TokenService::getModalToUse($this->messageToSend);
            $this->cmd->info('Currently using '.$getModalToUse['model'].' with '.$getModalToUse['estimatedCount'].' tokens ('.$getModalToUse['percent_used'].'% used)');
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
        $this->cmd->info('Total files '.$fileCount['total'].' with '.$fileCount['modified'].' modified');
    }

    private function getCurrentChatInformation(array $messages): void
    {
        $getModalToUse = TokenService::getModalToUse($messages);
        $this->cmd->info('Currently using '.$getModalToUse['model'].' with '.$getModalToUse['estimatedCount'].' tokens ('.$getModalToUse['percent_used'].'% used)');
    }

    public function chat(string $currentQuestion): void
    {

        $cmd = $this->cmd->ask($currentQuestion);
        $this->prepareChatMessage();

        $command = '';
        while ($command != 'commit') {
            $currentMessageToSend = [...$this->messageToSend, ChatMessageValueObject::make('user', $cmd)];

            $this->streamConversation($currentMessageToSend);

            $this->cmd->newLine(2);
            $this->getCurrentChatInformation($currentMessageToSend);

            $this->cmd->comment('Edit: (space then press Tab), Add message and continue conversation: (add), Accept Response: (commit), Discard and go to main menu: (exit)');
            $command = $this->cmd->anticipate('Accept Response?', function (string $input) use ($cmd) {
                return [' '.$cmd, 'commit'];
            });

            if ($command == 'exit') {
                return;
            } elseif ($command == 'add') {
                $this->addUserAssistantMessage($cmd);
                $cmd = $this->cmd->ask('User: ');
            } else {
                $cmd = $command;
            }

        }

        $this->cmd->info('Committing Response');
        $this->updateCurrentMessageToSend($cmd);

        $this->streamConversation($this->currentMessageToSend, true);

        if ($this->finalResponse['finish_reason'] == 'function_call') {
            $this->executeFunctionCall();
        }
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

        $this->messageToSend[] = ChatMessageValueObject::make($this->systemMessage->getRole(), $this->systemMessage->getContent());

        if (! empty($this->attachedFiles)) {
            $filesToSend = "# Anythings Below This Line Is what we have currently built. Seperated by \"\"\"\n\n";
            foreach ($this->attachedFiles as $file) {
                $filesToSend .= $file->getContent().'"""'."\n";
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
        ];

        return implode("\n", $message);
    }

    private function streamConversation(array $currentMessageToSend, bool $withFunctions = false): void
    {
        $getModalToUse = TokenService::getModalToUse($currentMessageToSend);

        $streamMessage = [
            'model' => $getModalToUse['model'],
            'messages' => $currentMessageToSend,
        ];

        if (! empty($withFunctions)) {

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

            $this->cmd->getOutput()->write($content.$function_call.$arguments);
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
        $this->messageToSend[] = ChatMessageValueObject::make('assistant', $this->finalResponse['content']);
    }

    private function updateCurrentMessageToSend(string $cmd): void
    {
        $this->currentMessageToSend = $this->messageToSend;

        $this->currentMessageToSend[] = ChatMessageValueObject::make('assistant', $this->finalResponse['content']);
        $this->currentMessageToSend[] = ChatMessageValueObject::make('user', "Instructions:\n *Make the above changes using the save_files function.\n *Respond only with the function call.\n * respond with the WHOLE file. \n *Do not truncate any code.");
    }

    private function executeFunctionCall(): void
    {
        $functionName = (string) $this->finalResponse['function_call']['name'];
        if (! empty($this->functions[$functionName])) {
            $functionResult = $this->functions[$functionName]->doFunction((string) $this->finalResponse['function_call']['arguments'], $this->getAttachedFiles());
            $this->setAttachedFiles($functionResult);
        }

        //ToDo: Add error handling
    }

    public function setSynthCommand(SynthCommand $cmd): void
    {
        $this->cmd = $cmd;

        //Set Synth controller for all classes
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
}
