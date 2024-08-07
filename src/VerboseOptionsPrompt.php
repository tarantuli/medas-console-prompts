<?php

declare(strict_types=1);

namespace Medas\ConsolePrompts;

use Medas\Console\{Blocks, Printable, Text};

readonly class VerboseOptionsPrompt extends OptionsPrompt
{
    public function __construct(
        private array          $elaborateOptions,
        private Printable|null $message = null,
        private Printable|null $prompt = null,
        private Printable|null $invalidResponseMessage = null,
    )
    {
        $options = [];
        $compoundMessage = new Blocks($this->message);

        foreach ($this->elaborateOptions as $option => $optionMessage) {
            if ($options !== []) {
                $compoundMessage->blocks[] = Text::create(', ');
            }

            $options[] = $option;
            $compoundMessage->blocks[] = $optionMessage;
            $compoundMessage->blocks[] = Text::create(' [' . $option . ']');
        }

        parent::__construct(
            $options,
            $compoundMessage,
            $this->prompt,
            $this->invalidResponseMessage
        );
    }
}
