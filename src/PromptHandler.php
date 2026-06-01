<?php

declare(strict_types=1);

namespace Medas\ConsolePrompts;

use Medas\Console\Printer;
use Medas\Core\Attributes\Service;

#[Service]
readonly class PromptHandler
{
    /**
     * @var resource
     */
    private mixed $stdin;

    public function __construct(
        private Printer $printer,
    )
    {
        $stdin = fopen('php://stdin', 'r');

        if ($stdin === false) {
            throw new \RuntimeException('Failed to open stdin for reading.');
        }

        $this->stdin = $stdin;
    }

    public function __destruct()
    {
        if (is_resource($this->stdin)) {
            fclose($this->stdin);
        }
    }

    public function handle(Prompt $prompt): Result
    {
        $result = new Result();

        if ($prompt->message()) {
            $this->printer->printLine($prompt->message());
        }

        do {
            if ($prompt->prompt()) {
                $this->printer->print($prompt->prompt());
            }

            $response = fgets($this->stdin);

            if ($response === false) {
                throw new \RuntimeException('Failed to read from stdin: stream closed or EOF reached.');
            }

            if ($prompt->doTrim()) {
                $response = trim($response);
            }

            if ($response === '' && $prompt->default() !== null) {
                $response = $prompt->default();
            }

            $this->processResponse($prompt, $response, $result);
        } while (!$result->gotResponse);

        return $result;
    }

    public function handleTest(Prompt $prompt, array $responses): Result
    {
        $result = new Result();

        foreach ($responses as $response) {
            if ($prompt->doTrim()) {
                $response = trim($response);
            }

            if ($response === '' && $prompt->default() !== null) {
                $response = $prompt->default();
            }

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
                $this->printer->printLine($prompt->invalidResponseMessage());
            }

            $result->invalidResponses[] = $response;
        }
    }
}
