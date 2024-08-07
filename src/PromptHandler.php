<?php

declare(strict_types=1);

namespace Medas\ConsolePrompts;

use Medas\Console\Printer;
use Medas\Core\Attributes\Service;

#[Service]
readonly class PromptHandler
{
    private mixed $stdin;

    public function __construct(
        private Printer $printer,
    )
    {
        $this->stdin = fopen('php://stdin', 'r');
    }

    public function __destruct()
    {
        fclose($this->stdin);
    }

    public function handle(Prompt $prompt): Result
    {
        $result = new Result();

        if ($prompt->message()) {
            $this->printer->print($prompt->message());
        }

        do {
            if ($prompt->prompt()) {
                $this->printer->print($prompt->prompt());
            }

            $response = trim(fgets($this->stdin));

            $this->processResponse($prompt, $response, $result);
        } while (!$result->gotResponse);

        return $result;
    }

    public function handleTest(Prompt $prompt, array $responses): Result
    {
        $result = new Result();

        foreach ($responses as $response) {
            $this->processResponse($prompt, $response, $result);

            if ($result->gotResponse) {
                break;
            }
        }

        return $result;
    }

    private function processResponse(Prompt $prompt, string $response, Result $result): void
    {
        if ($prompt->isValid($response)) {
            $result->gotResponse = true;
            $result->response = $response;
        }
        else {
            if ($prompt->invalidResponseMessage()) {
                $this->printer->print($prompt->invalidResponseMessage());
            }

            $result->invalidResponses[] = $response;
        }
    }
}
