<?php

declare(strict_types=1);

namespace Blinq\Synth\Services;

use Blinq\Synth\Exceptions\MissingOpenAIKeyException;
use Blinq\Synth\Helpers\ChatTokenService;
use Blinq\Synth\Interfaces\PromptInterface;
use Blinq\Synth\ValueObjects\AttachedFileValueObject;
use Blinq\Synth\ValueObjects\ChatMessageValueObject;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use OpenAI;
use OpenAI\Client;

final class AiService
{
    public Client   $openaiClient;

    protected array $messageToSend;

    protected array $functions = [];

    protected array $attachedFiles = [];

    public function __construct(
        private PromptInterface $promptInterface,
        array|string $attachedFiles = '',
    ) {

        if ( ! config('synth.openai_key')) {
            throw MissingOpenAIKeyException::make();
        }

        $this->openaiClient = OpenAI::factory()
            ->withApiKey(config('synth.openai_key'))
            ->withHttpClient(new GuzzleClient(['timeout' => 90.0])) // default: HTTP client found using PSR-18 HTTP Client Discovery
            ->make();

        if (is_string($attachedFiles)) {
            $this->attachedFiles = [$attachedFiles];
        }

        if (is_array($attachedFiles)) {
            $this->attachedFiles = $attachedFiles;
        }

        //use the promptInterface to get the functions
        foreach ($promptInterface->getFunctions() as $function) {
            $this->functions[$function->getName()] = $function;
        }

    }

    public function chat(string $question)
    {
        $this->prepareChatMessage();

        $currentMessageToSend = [...$this->messageToSend, ChatMessageValueObject::make('user', $question)];

        $response = $this->create($currentMessageToSend);

        $response = $response->toArray();

        return [
            ...$response['choices'][0],
            'model' => $response['model'],
            'total_tokens' => $response['usage']['total_tokens'],
        ];
    }

    private function prepareChatMessage(): void
    {

        $this->messageToSend = [];

        $this->messageToSend[] = ChatMessageValueObject::make('system', $this->promptInterface->getSystem()->getContent());

        if ( ! empty($this->attachedFiles)) {
            $filesToSend = "# Anything below this line is what we have currently built. Files are seperated by \"\"\"\n\n";
            foreach ($this->attachedFiles as $file) {
                $file = AttachedFileValueObject::make($file, file_get_contents(base_path($file)), false);
                $filesToSend .= $file->getContent() . '"""' . "\n";
            }
            $this->messageToSend[] = ChatMessageValueObject::make('user', $filesToSend);
        }
    }

    private function create(array $currentMessageToSend)
    {

        $modalToExclude = [];
        while (true) {
            $message = [
                'model' => '',
                'messages' => $currentMessageToSend,
            ];
            if ( ! empty($this->functions)) {

                $functions = [];
                foreach ($this->functions as $function) {
                    $functions[] = $function->getFunctionJson();
                }

                $message['functions'] = $functions;
            }

            $getModalToUse = ChatTokenService::getModalToUse($message, $modalToExclude);
            $message['model'] = $getModalToUse['model'];

            try {
                return $this->openaiClient->chat()->create($message);
            } catch (Exception $e) {
                dd($e);
                //throw $th;
            }

        }

    }
}
