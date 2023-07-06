<?php

declare(strict_types=1);

namespace Blinq\Synth\Services;

use Blinq\Synth\Exceptions\MissingOpenAIKeyException;
use Blinq\Synth\Helpers\ChatTokenService;
use Blinq\Synth\Interfaces\PromptInterface;
use Blinq\Synth\ValueObjects\AttachedFileValueObject;
use Blinq\Synth\ValueObjects\ChatMessageValueObject;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Arr;
use OpenAI;
use OpenAI\Client;

class AiService
{
    public Client   $openaiClient;

    protected array $finalResponse;

    protected array $functions;

    protected array $messageToSend;

    public function __construct(
        private PromptInterface $systemMessage,
        private array $attachedFiles = [],
    ) {

        if ( ! config('synth.openai_key')) {
            throw MissingOpenAIKeyException::make();
        }

        $this->openaiClient = OpenAI::factory()
            ->withApiKey(config('synth.openai_key'))
            ->withHttpClient(new GuzzleClient(['timeout' => 90.0])) // default: HTTP client found using PSR-18 HTTP Client Discovery
            ->make();

    }

    public function chat(string $currentQuestion)
    {
        $this->prepareChatMessage();

        $currentMessageToSend = [...$this->messageToSend, ChatMessageValueObject::make('user', $currentQuestion)];

        $response = $this->create($currentMessageToSend);
        $response = $response->choices[0]->toArray();

        return $response;

    }

    public function updateFinalResponse(array $response): void
    {
        $content = Arr::get($response, 'delta.content', '');
        $functionCall = Arr::get($response, 'delta.function_call.name', '');
        $arguments = Arr::get($response, 'delta.function_call.arguments', '');

        if (Arr::get($response, 'delta.role')) {
            $this->finalResponse['role'] = Arr::get($response, 'delta.role');
        }

        if (Arr::get($response, 'finish_reason')) {
            $this->finalResponse['finish_reason'] = Arr::get($response, 'finish_reason');
        }

        $this->finalResponse['content'] = $this->finalResponse['content']->append($content);
        $this->finalResponse['function_call']['name'] = $this->finalResponse['function_call']['name']->append($functionCall);
        $this->finalResponse['function_call']['arguments'] = $this->finalResponse['function_call']['arguments']->append($arguments);

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

        $this->messageToSend[] = ChatMessageValueObject::make('system', $this->addGlobalInstructions($this->systemMessage->getSystem()->getContent()));

        if ( ! empty($this->attachedFiles)) {
            $filesToSend = "# Anythings below this line is what we have currently built. Files are seperated by \"\"\"\n\n";
            foreach ($this->attachedFiles as $file) {
                $file = AttachedFileValueObject::make($file, file_get_contents(base_path($file)), false);
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

    private function create(array $currentMessageToSend, bool $withFunctions = false)
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

        return $this->openaiClient->chat()->create($streamMessage);

    }
}
