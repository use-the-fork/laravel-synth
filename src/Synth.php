<?php

namespace Blinq\Synth;

use Blinq\LLM\Entities\ChatMessage;
use Blinq\LLM\Entities\ChatStream;
use Blinq\LLM\Exceptions\ApiException;
use Blinq\Synth\Controllers\SynthController;
use Blinq\Synth\Exceptions\MissingOpenAIKeyException;
use OpenAI;
use OpenAI\Client;

class Synth
{
    protected SynthController $synthController;

    public Client $ai;

    public $smallModel = 'gpt-3.5-turbo-0613';

    public $largeModel = 'gpt-3.5-turbo-16k-0613';

    public $model = 'gpt-3.5-turbo-0613';

    /**
     * @throws MissingOpenAIKeyException
     */
    public function __construct()
    {
        $this->model = config('synth.small_model', $this->smallModel);
        $this->smallModel = config('synth.small_model', $this->smallModel);
        $this->largeModel = config('synth.large_model', $this->largeModel);

        if (! config('synth.openai_key')) {
            throw MissingOpenAIKeyException::make();
        }
        $this->ai = OpenAI::client(config('synth.openai_key'));
    }

    public function setSynthController(): void
    {
        $this->synthController = app(SynthController::class);
    }

    public function loadSystemMessage(string $name)
    {

        $messageStore[0] = ['role' => 'user', 'content' => 'Hello!'];
        $this->ai->setSystemMessage(include __DIR__."/Prompts/$name.system.php");
    }

    public function chat(string $message, array $options = [])
    {
        try {
            $this->ai->chat($message, 'user', [
                'model' => $this->model,
                'stream' => true,
                ...$options,
            ]);
        } catch (ApiException $ex) {
            // ray($ex);

            if (str($ex->getMessage())->contains('maximum context length') && $this->model == $this->smallModel) {
                $this->cmd->error('Max context length exceeded, switching to large model');

                $this->model = $this->largeModel;

                return $this->chat($message, $options);
            } else {
                $this->cmd->error('OpenAI Error: '.$ex->getMessage());
            }
        }
    }

    public function handleExitSignal()
    {
        declare(ticks=1); // Allow posix signal handling

        pcntl_signal(SIGINT, function () {
            if ($this->ai->isBusy()) {
                $this->ai->cancelRequest();
            }
        });
    }

    public function handleStream()
    {
        $this->ai->addStreamHandler(function (ChatStream $x) {
            $this->cmd->getOutput()->write(
                $x->getMessage()?->content ?? ''
            );

            $this->cmd->getOutput()->write(
                ($x->getMessage()?->function_call['arguments'] ?? '')
            );

            if ($x->done) {
                $this->cmd->newLine(2);
            }
        });
    }

    public array $allowed = [
        'save_migrations',
        'save_files',
    ];

    public function handleFunctionsForLastMessage()
    {
        $lastMessage = $this->ai->getLastMessage();

        if (! $lastMessage) {
            return;
        }

        $this->handleFunctions($lastMessage);
    }

    public function handleFunctions(ChatMessage $message)
    {
        $functionCall = $message->function_call['name'] ?? null;
        $args = $message->function_call['arguments'] ?? null;

        if (! $functionCall) {
            return;
        }

        if (! Functions::isAllowed($functionCall)) {
            $this->cmd->error("Function $functionCall is not allowed");

            return;
        }

        if ($args) {
            $args = $this->fixSyntax($args);
            $parsed = json_decode($args, true);

            if (! $parsed) {
                $this->cmd->error('--------');
                $this->cmd->error($args);
                $this->cmd->error('--------');
                $this->cmd->error('The model returned JSON that did not parse, please try again!');

                return;
            }

            Functions::call($functionCall, $this->cmd, ...$parsed);
        }
    }

    public function fixSyntax(string $args)
    {
        // Fix some common errors in the output
        $args = str_replace('\\', '\\\\', $args);
        $args = str_replace('\\\\\\\\', '\\\\', $args);
        $args = str_replace('\\\\n', '\n', $args);
        $args = str_replace('\\\\"', '\\"', $args);
        // Replace \r\n with \n
        $args = str_replace('\\r\\n', '\n', $args);
        $args = str_replace('\r\n', '\n', $args);

        $args = str_replace(PHP_EOL, '', $args);

        return $args;
    }

    public function estimateTokenCount(): int
    {
        $wordCount = collect($this->ai->getHistory())
            ->reduce(function ($carry, ChatMessage $item) {
                return $carry + str($item->content)->explode(' ')->count();
            }, 0);

        return floor($wordCount * 0.75);
    }

    public function getLastMessage(): ?ChatMessage
    {
        return $this->ai->getLastMessage();
    }
}
