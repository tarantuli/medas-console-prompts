<?php

declare(strict_types=1);

namespace Medas\ConsolePromptsTest\Functional;

use Medas\ConsolePrompts\{OptionsPrompt, PromptHandler};
use PHPUnit\Framework\TestCase;

class PromptHandlerTest extends TestCase
{
    public function testOptionsPrompt(): void
    {
        $prompt = new OptionsPrompt(['a', 'b', 'c']);
        $result = service(PromptHandler::class)->handleTest($prompt, ['', 'd', 'a', 'z']);

        self::assertTrue($result->gotResponse);
        self::assertEquals('a', $result->response);
        self::assertEquals(['', 'd'], $result->invalidResponses);
    }
}
