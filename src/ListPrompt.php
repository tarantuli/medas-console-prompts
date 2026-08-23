<?php

declare(strict_types=1);

namespace Medas\ConsolePrompts;

use Medas\Console\{Formats\SafeColor, Printable, Text};

/**
 * Collects a growing list of free-text responses, one per line, until the user submits a
 * blank line (and at least `minItems` have been collected). Unlike `TextPrompt`, which reads
 * a single line, this repeats the read/validate loop and accumulates every accepted line into
 * `ListResult::$items`.
 */
readonly class ListPrompt
{
    public function __construct(
        private \Closure|null           $validator = null,
        private Printable|null          $message = null,
        private Printable|\Closure|null $itemPrompt = null,
        private Printable|null          $invalidResponseMessage = null,
        private Printable|null          $notEnoughItemsMessage = null,
        private int                     $minItems = 1,
        private bool                    $doTrim = true,
    )
    {
        if ($this->minItems < 0) {
            throw new Exceptions\ListMinItemsCannotBeNegative();
        }
    }

    public function message(): Printable|null
    {
        return $this->message;
    }

    /** The prompt shown for the Nth item about to be entered (1-based). */
    public function itemPrompt(int $itemNumber): Printable
    {
        if ($this->itemPrompt instanceof \Closure) {
            return ($this->itemPrompt)($itemNumber);
        }

        return $this->itemPrompt ?? Text::create($itemNumber . ') ', SafeColor::DarkYellow);
    }

    public function isValid(string $response): bool
    {
        return $this->validator ? ($this->validator)($response) : true;
    }

    public function invalidResponseMessage(): Printable|null
    {
        return $this->invalidResponseMessage;
    }

    public function notEnoughItemsMessage(): Printable|null
    {
        return $this->notEnoughItemsMessage;
    }

    public function minItems(): int
    {
        return $this->minItems;
    }

    public function doTrim(): bool
    {
        return $this->doTrim;
    }
}
