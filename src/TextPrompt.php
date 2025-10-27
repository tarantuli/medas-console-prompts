<?php

declare(strict_types=1);

namespace Medas\ConsolePrompts;

use Medas\Console\Printable;

readonly class TextPrompt implements Prompt
{
    public function __construct(
        private \Closure|null  $validator,
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
        return $this->validator ? ($this->validator)($response) : true;
    }

    public function invalidResponseMessage(): Printable|null
    {
        return $this->invalidResponseMessage;
    }
}
