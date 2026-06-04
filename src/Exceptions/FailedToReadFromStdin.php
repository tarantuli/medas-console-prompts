<?php

declare(strict_types=1);

namespace Medas\ConsolePrompts\Exceptions;

use Medas\Core\Exceptions\BaseException;

class FailedToReadFromStdin extends BaseException
{
    public function pattern(): string
    {
        return 'failed to read from stdin';
    }
}
