<?php

declare(strict_types=1);

namespace Medas\ConsolePrompts;

use Medas\Console\Printable;

readonly class OptionsPrompt implements Prompt
{
    public function __construct(
        private array          $options,
        private Printable|null $message = null,
        private Printable|null $prompt = null,
        private Printable|null $invalidResponseMessage = null,
    )
    {
    }

    public function message(): Printable|null
    {
        return $this->message;
    }

    public function prompt(): Printable|null
    {
        return $this->prompt;
    }

    public function isValid(string $response): bool
    {
        return in_array($response, $this->options, true);
    }

    public function invalidResponseMessage(): Printable|null
    {
        return $this->invalidResponseMessage;
    }

    public function default(): string|null
    {
        return null;
    }

    public function doTrim(): bool
    {
        return true;
    }
}
