<?php

declare(strict_types=1);

namespace Medas\ConsolePrompts;

use Medas\Console\{Formats\SafeColor, Printer, Text};
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
            throw new Exceptions\FailedToReadFromStdin();
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
            $message = $prompt->message();
            $blocks = [$message];

            if ($prompt instanceof OptionsPrompt) {
                $blocks = $this->addOptions(
                    $blocks,
                    $message instanceof Text ? $message->format : [],
                    $prompt->options()
                );
            }

            $this->printer->printLine(...$blocks);
        }

        do {
            if ($prompt->prompt()) {
                $this->printer->print($prompt->prompt());
            }
            else {
                $this->printer->print(Text::create('> ', SafeColor::DarkYellow));
            }

            $response = fgets($this->stdin);

            if ($response === false) {
                throw new Exceptions\FailedToReadFromStdin();
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

    private function addOptions(array $blocks, array $messageFormat, array $options): array
    {
        $blocks[] = Text::create(' (', ...$messageFormat);

        foreach ($options as $i => $option) {
            if ($i > 0) {
                $blocks[] = Text::create(', ', ...$messageFormat);
            }

            $blocks[] = Text::create((string) $option, SafeColor::Cyan);
        }

        $blocks[] = Text::create(')', ...$messageFormat);

        return $blocks;
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
            else {
                $this->printer->printLine(Text::create('Invalid response, please retry:', SafeColor::Red));
            }

            $result->invalidResponses[] = $response;
        }
    }

    /**
     * Repeatedly reads one line at a time, collecting every accepted line into
     * `ListResult::$items`, until the user submits a blank line (accepted only once
     * `ListPrompt::minItems()` items have been collected).
     */
    public function handleList(ListPrompt $prompt): ListResult
    {
        $result = new ListResult();

        if ($prompt->message()) {
            $this->printer->printLine($prompt->message());
        }

        while (true) {
            $itemNumber = count($result->items) + 1;

            $this->printer->print($prompt->itemPrompt($itemNumber));

            $response = fgets($this->stdin);

            if ($response === false) {
                throw new Exceptions\FailedToReadFromStdin();
            }

            if ($prompt->doTrim()) {
                $response = trim($response);
            }

            if (!$this->processListResponse($prompt, $response, $result)) {
                break;
            }
        }

        return $result;
    }

    /**
     * Like `handleList()`, but consumes a pre-supplied array of lines instead of stdin, for
     * testing `ListPrompt` logic. A blank entry in `$responses` terminates the list exactly
     * like an empty line from stdin would.
     */
    public function handleListTest(ListPrompt $prompt, array $responses): ListResult
    {
        $result = new ListResult();

        foreach ($responses as $response) {
            if ($prompt->doTrim()) {
                $response = trim($response);
            }

            if (!$this->processListResponse($prompt, $response, $result)) {
                break;
            }
        }

        return $result;
    }

    /** @return bool whether to keep collecting more items */
    private function processListResponse(ListPrompt $prompt, string $response, ListResult $result): bool
    {
        if ($response === '') {
            if (count($result->items) >= $prompt->minItems()) {
                return false;
            }

            $this->printer->printLine($prompt->notEnoughItemsMessage() ?? Text::create(
                'Please enter at least ' . $prompt->minItems() . ' item(s).',
                SafeColor::Red
            ));

            return true;
        }

        if (!$prompt->isValid($response)) {
            $this->printer->printLine($prompt->invalidResponseMessage() ?? Text::create(
                'Invalid response, please retry:',
                SafeColor::Red
            ));

            $result->invalidResponses[] = $response;

            return true;
        }

        $result->items[] = $response;

        return true;
    }
}
