<?php

declare(strict_types=1);

namespace Medas\ConsolePrompts;

use Medas\Console\Printable;

interface Prompt
{
    public function message(): Printable|null;

    public function prompt(): Printable|null;

    public function isValid(string $response): bool;

    public function invalidResponseMessage(): Printable|null;
}
