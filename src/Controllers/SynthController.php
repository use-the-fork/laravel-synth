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
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Arr;
use OpenAI;
use OpenAI\Client;

class SynthController
{
    public $cmd;

    public ChatMessageValueObject $systemMessage;

    private Client $openaiClient;

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
    ) {

        if (! config('synth.openai_key')) {
            throw MissingOpenAIKeyException::make();
        }

        $this->openaiClient = OpenAI::factory()
            ->withApiKey(config('synth.openai_key'))
            ->withHttpClient($client = new GuzzleClient(['timeout' => 30.0])) // default: HTTP client found using PSR-18 HTTP Client Discovery
            ->make();

    }

    public function chat(string $cmd): void
    {
        //prepare chat message
        $messageToSend = [];
        $messageToSend[] = $this->systemMessage;
        if (! empty($this->attachedFiles)) {
            $filesToSend = "Here are the files I will be referencing:\n\"\"\"\n";
            foreach ($this->attachedFiles as $file) {
                $filesToSend .= $file->getContent().'"""'."\n";
            }
            $messageToSend[] = ChatMessageValueObject::make('user', $filesToSend);
        }

        $command = '';
        while ($command != 'commit') {
            $currentMessageToSend = [...$messageToSend, ChatMessageValueObject::make('user', $cmd)];
            $getModalToUse = TokenService::getModalToUse($currentMessageToSend);

            $stream = $this->openaiClient->chat()->createStreamed([
                'model' => $getModalToUse['model'],
                'messages' => $currentMessageToSend,
            ]);

            $finalResponse = [
                'role' => '',
                'content' => str(''),
                'function_call' => [
                    'name' => str(''),
                    'arguments' => str(''),
                ],
                'finish_reason' => '',
            ];
            foreach ($stream as $response) {

                $response = $response->choices[0]->toArray();

                if (Arr::get($response, 'delta.role')) {
                    $finalResponse['role'] = Arr::get($response, 'delta.role');
                }

                if (Arr::get($response, 'finish_reason')) {
                    $finalResponse['finish_reason'] = Arr::get($response, 'finish_reason');
                }

                $content = Arr::get($response, 'delta.content', '');
                $function_call = Arr::get($response, 'delta.function_call.name', '');
                $arguments = Arr::get($response, 'delta.function_call.arguments', '');

                $finalResponse['content'] = $finalResponse['content']->append(Arr::get($response, 'delta.content'));

                $this->cmd->getOutput()->write($content.$function_call.$arguments);
            }
            $this->cmd->newLine(2);

            $this->cmd->info("Edit: (space then press Tab), Add message and continue conversation: (add), Accept Response: (commit)\n");
            $command = $this->cmd->anticipate('Accept Response?', function (string $input) use ($cmd) {
                return [' '.$cmd, 'commit'];
            });

            if ($command == 'add') {
                $currentMessageToSend[] = ChatMessageValueObject::make('assistant', $finalResponse['content']);
                $cmd = $this->cmd->ask('User: ');
            }
        }

        $this->cmd->info('Committing Response');
        $currentMessageToSend[0] = ChatMessageValueObject::make('system', $currentMessageToSend[0]->getContent()."\nAddition Instructions:\n * use the save_files function to make any changes. Respond only with the function call.\n * respond with the WHOLE file.\n * Do not truncate any code.");
        $currentMessageToSend[] = ChatMessageValueObject::make('assistant', $finalResponse['content']);
        $currentMessageToSend[] = ChatMessageValueObject::make('user', 'Make the above changes using the save_files function');

        $getModalToUse = TokenService::getModalToUse($currentMessageToSend);

        $functions = [];
        foreach ($this->functions as $function) {
            $functions[] = $function->getFunctionJson();
        }

        $stream = $this->openaiClient->chat()->createStreamed([
            'model' => $getModalToUse['model'],
            'messages' => $currentMessageToSend,
            'functions' => $functions,
        ]);

        $finalResponse = [
            'role' => '',
            'content' => str(''),
            'function_call' => [
                'name' => str(''),
                'arguments' => str(''),
            ],
            'finish_reason' => '',
        ];
        foreach ($stream as $response) {

            $response = $response->choices[0]->toArray();

            if (Arr::get($response, 'finish_reason')) {
                $finalResponse['finish_reason'] = Arr::get($response, 'finish_reason');
            }

            $content = Arr::get($response, 'delta.content', '');
            $function_call = Arr::get($response, 'delta.function_call.name', '');
            $arguments = Arr::get($response, 'delta.function_call.arguments', '');

            $finalResponse['content'] = $finalResponse['content']->append(Arr::get($response, 'delta.content'));
            $finalResponse['function_call']['name'] = $finalResponse['function_call']['name']->append(Arr::get($response, 'delta.function_call.name'));
            $finalResponse['function_call']['arguments'] = $finalResponse['function_call']['arguments']->append(Arr::get($response, 'delta.function_call.arguments'));

            $this->cmd->getOutput()->write($content.$function_call.$arguments);
        }

        if ($finalResponse['finish_reason'] == 'function_call') {

            if (! empty($this->functions[(string) $finalResponse['function_call']['name']])) {
                $functionResult = $this->functions[(string) $finalResponse['function_call']['name']]->doFunction((string) $finalResponse['function_call']['arguments'], $this->getAttachedFiles());
                $this->setAttachedFiles($functionResult);
            }

            //ToDo: Add error handling

        }
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

    public function addAttachedFile($key, $value): void
    {
        $base = basename($key);
        $this->cmd->comment("Attaching {$base}");
        $this->attachedFiles[$key] = AttachedFileValueObject::make($key, $value);
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
}
