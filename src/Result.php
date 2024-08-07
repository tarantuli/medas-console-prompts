<?php

declare(strict_types=1);

namespace Medas\ConsolePrompts;

class Result
{
    public bool $gotResponse = false;
    public string $response;
    public array $invalidResponses = [];
}
