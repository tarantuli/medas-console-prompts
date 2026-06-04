<?php

declare(strict_types=1);

namespace Medas\ConsolePrompts\Exceptions;

use Medas\Core\Exceptions\BaseException;

class OptionsCannotBeEmpty extends BaseException
{
    public function pattern(): string
    {
        return 'prompt options cannot be empty';
    }
}
