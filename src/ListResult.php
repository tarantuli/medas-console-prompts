<?php

declare(strict_types=1);

namespace Medas\ConsolePrompts;

class ListResult
{
    /** @var string[] */
    public array $items = [];

    /** @var string[] */
    public array $invalidResponses = [];
}
