<?php

declare(strict_types=1);

namespace Medas\ConsolePrompts\Exceptions;

use Medas\Core\Exceptions\BaseException;

class ListMinItemsCannotBeNegative extends BaseException
{
    public function pattern(): string
    {
        return 'list prompt minItems cannot be negative';
    }
}
