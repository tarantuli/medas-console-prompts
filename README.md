# medas-console-prompts

Part of the [Medas framework](https://github.com/tarantuli/medas-core).

## Description

A structured prompt system built on top of `medas-console`. Where `medas-console-printer`'s `ConsoleReader` is a low-level stdin reader, this package adds a `Prompt` interface with explicit message, prompt line, validation, and invalid-response-message slots, all expressed as `Printable` values so they are rendered through the normal `Printer` pipeline.

`PromptHandler` drives the interaction loop: it prints the optional preamble message once, then repeatedly prints the prompt line, and reads a line from stdin until `Prompt::isValid()` returns `true`. Invalid responses are collected in `Result::$invalidResponses` for post-processing if needed. A `handleTest()` method accepts a pre-supplied array of responses, making prompt logic unit-testable without stdin.

Three prompt implementations are provided:

| Class                  | Accepts                                                                                   |
|------------------------|-------------------------------------------------------------------------------------------|
| `TextPrompt`           | Any input; optional `\Closure` validator                                                  |
| `OptionsPrompt`        | Only values present in a supplied `array`                                                 |
| `VerboseOptionsPrompt` | Like `OptionsPrompt`, but auto-renders each option as `Label [key]` inline in the message |

A fourth type, `ListPrompt`, is not a `Prompt` at all -- it collects a *sequence* of free-text lines rather than validating a single response, so it is driven through its own `PromptHandler::handleList()` entry point (with a matching `handleListTest()`).

## Usage

### Package developer context

Register the package and inject `PromptHandler` into any command that needs interactive input:

```php
use Medas\ConsolePrompts\ConsolePromptsPackage;

ConsolePromptsPackage::instance();
```

**`TextPrompt` — free-form input with a validator:**

```php
use Medas\Console\Text;
use Medas\Console\Formats\SafeColor;
use Medas\ConsolePrompts\{PromptHandler, TextPrompt};
use Medas\Core\Attributes\Service;

#[Service]
readonly class CreateUserCommand extends BaseConsoleCommand
{
    public function __construct(
        private PromptHandler $promptHandler,
        private MyGroup       $group,
    ) {}

    public function process(CommandInput $input): void
    {
        $result = $this->promptHandler->handle(new TextPrompt(
            validator: fn(string $v) => str_contains($v, '@'),
            message: Text::create('Enter the new user\'s email address.', SafeColor::LightYellow),
            prompt: Text::create('Email: '),
            invalidResponseMessage: Text::create('That doesn\'t look like a valid email address.', SafeColor::LightRed),
        ));

        $email = $result->response;

        // ...
    }
}
```

**`OptionsPrompt` — constrained to a fixed set of values:**

```php
use Medas\ConsolePrompts\{PromptHandler, OptionsPrompt};

$result = $this->promptHandler->handle(new OptionsPrompt(
    options: ['y', 'n'],
    message: Text::create('Are you sure you want to delete this record?'),
    prompt: Text::create('Confirm [y/n]: '),
    invalidResponseMessage: Text::create('Please enter y or n.', SafeColor::LightRed),
));

if ($result->response === 'y') {
    $this->deleteRecord();
}
```

**`VerboseOptionsPrompt` — self-describing option menu:**

```php
use Medas\ConsolePrompts\{PromptHandler, VerboseOptionsPrompt};

// The message is built automatically from the $elaborateOptions keys and labels:
//   Keep existing value [k],  Overwrite [o],  Skip [s]
$result = $this->promptHandler->handle(new VerboseOptionsPrompt(
    elaborateOptions: [
        'k' => 'Keep existing value',
        'o' => 'Overwrite',
        's' => 'Skip',
    ],
    message: Text::create('A value already exists for this key.'),
    prompt: Text::create('Choice: '),
    invalidResponseMessage: Text::create('Please enter k, o, or s.', SafeColor::LightRed),
));

match ($result->response) {
    'k' => $this->keep(),
    'o' => $this->overwrite(),
    's' => $this->skip(),
};
```

**`ListPrompt` — free-text entries collected until a blank line:**

Where the other prompt types resolve to a single `string`, `ListPrompt` reads one line at a
time and keeps collecting them into an array until the user submits a blank line -- useful
for "type as many of these as you like" input (tags, aliases, a manual breakdown of a name
into parts, etc.). It is handled via `PromptHandler::handleList()`, which returns a
`ListResult` (`$items`, `$invalidResponses`) instead of a `Result`.

```php
use Medas\Console\Text;
use Medas\ConsolePrompts\{ListPrompt, PromptHandler};

$result = $this->promptHandler->handleList(new ListPrompt(
    message: Text::create('Type each artist name, one per line, then press enter on an empty line to finish.'),
));

$artistNames = $result->items;
```

By default at least one item is required before a blank line is accepted (`minItems: 1`);
raise or lower that with the `minItems` constructor argument. A `validator` closure, an
`invalidResponseMessage`, and a `notEnoughItemsMessage` work exactly like their `TextPrompt`
counterparts, applied per line. The per-item prompt text defaults to a numbered `1) `, `2) `,
etc., and can be overridden with a fixed `Printable` or a `fn(int $itemNumber): Printable`
closure via the `itemPrompt` argument.

**Implementing a custom `Prompt`:**

```php
use Medas\Console\{Printable, Text};
use Medas\Console\Formats\SafeColor;
use Medas\ConsolePrompts\Prompt;

readonly class PasswordPrompt implements Prompt
{
    public function message(): Printable|null
    {
        return Text::create('Choose a password (min 12 characters).', SafeColor::LightYellow);
    }

    public function prompt(): Printable|null
    {
        return Text::create('Password: ');
    }

    public function isValid(string $response): bool
    {
        return mb_strlen($response) >= 12;
    }

    public function invalidResponseMessage(): Printable|null
    {
        return Text::create('Password must be at least 12 characters.', SafeColor::LightRed);
    }
}

$result = $this->promptHandler->handle(new PasswordPrompt());
$password = $result->response;
```

**Testing prompt logic without stdin:**

```php
use Medas\ConsolePrompts\{PromptHandler, OptionsPrompt};

// handleTest() runs through the supplied responses in order and returns
// as soon as one passes validation, without touching stdin.
$result = $promptHandler->handleTest(
    new OptionsPrompt(options: ['y', 'n']),
    responses: ['maybe', 'yes', 'y'],
);

// $result->response      === 'y'
// $result->invalidResponses === ['maybe', 'yes']
```

`ListPrompt` has the same kind of stdin-free test double, `handleListTest()`, which consumes
a pre-supplied array of lines and treats a blank entry in that array exactly like a blank
line from stdin:

```php
$result = $promptHandler->handleListTest(
    new ListPrompt(),
    responses: ['Jam', 'Spoon', ''],
);

// $result->items === ['Jam', 'Spoon']
```

**Reading the `Result`:**

```php
$result = $this->promptHandler->handle($prompt);

// The accepted response
$value = $result->response;

// All responses that failed isValid() before the accepted one
$rejected = $result->invalidResponses;
```

`ListPrompt` reads back the same way, via `ListResult::$items` and `::$invalidResponses`
instead of a single `$response`.

### Backend user context

Prompts are driven entirely by command logic — there is no standalone CLI for this package. They appear as interactive steps inside commands:

```
A value already exists for this key.
  Keep existing value [k],  Overwrite [o],  Skip [s]
Choice: x
Please enter k, o, or s.
Choice: o
```

A `ListPrompt` step looks like this instead, ending as soon as an empty line is submitted:

```
Type each artist name, one per line, then press enter on an empty line to finish.
1) Jam
2) Spoon
3)
```

The loop keeps re-prompting until a valid response is given. There is no timeout or maximum retry count built in; commands that need those behaviours should implement a custom `Prompt`.
