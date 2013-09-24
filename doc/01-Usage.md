Usage
=====

> You should familiarize yourself with Symfony Console before proceeding. Since
> Go is built on it, you will be using its library to manage input and output.

Quick overview:

- To use Go, you must create a task file called `Gofile`.
- Your task file must be in the current working directory.
- The task file is simply a PHP script with access to special functions.

Creating a Task File
--------------------

> You may already be familiar with the process if you use Cakefiles.

Inside your task file, you will be making calls to a function called `task()`
to register your tasks with Go. Each call registers a new task, but beware that
creating a new task with the same name will replace the previous definition. A
simple task is shown below:

```php
<?php

task(
    'taskName',
    'My task description',
    function () {
        echo "Hello!\n";
    }
);
```

To run `taskName`, you simply pass it as an argument to Go:

```sh
user@host:~$ go taskName
Hello!
user@host:~$
```

Accepting Arguments and Options
-------------------------------

You may manually handle your input, or you may use the built-in input management
library from Symfony Console. Go simplifies the process of accepting input using
Console by making two functions available: `arg()` and `option()`. Each one may
be called zero or more times after you create your task.

> Note that these functions must be called immediately after creating the
> task you want to augment. Calls to `arg()` and `option()` are context
> sensitive.


### `void arg($name, $mode = null, $description = '', $default = null)`

- `$name` &mdash; The name of your argument.
- `$mode` &mdash; Zero or more of the following options:
    - `ARG_IS_ARRAY` &mdash; Multiple values may be provided for the argument.
    - `ARG_IS_OPTIONAL` &mdash; The argument is optional.
    - `ARG_IS_REQUIRED` &mdash; The argument is required.
- `$description` &mdash; A short description of the argument.
- `$default` &mdash; The default value.

```php
<?php

use Symfony\Console\Component\Input\InputInterface as In;

task(
    'hello',
    'Say hello',
    function (In $in) {
        printf("Hello, %s!\n", $in->getArgument('name'));
    }
);

arg('name', ARG_IS_OPTIONAL, 'Your name', 'world');
```

```sh
user@host:~$ bin/go hello
Hello, world!
user@host:~$ bin/go hello User
Hello, User!
```

### `void option($name, $shortcut, $mode = null, $description = '', $default = null)`

- `$name` &mdash; The name of your option.
- `$shortcut` &mdash; The short name of your option.
- `$mode` &mdash; Zero or more of the following options:
    - `OPT_IS_ARRAY` &mdash; Multiple values may be provided for the option.
    - `OPT_IS_OPTIONAL` &mdash; The value is not required for the option.
    - `OPT_IS_REQUIRED` &mdash; The value is required for the option.
    - `OPT_NO_VALUE` &mdash; There is no value to give. (Think boolean flags.)
- `$description` &mdash; A short description of the option.
- `$default` &mdash; The default value.

```php
<?php

use Symfony\Console\Component\Input\InputInterface as In;

task(
    'hello',
    'Say hello',
    function (InputInterface $in) {
        printf(
            "Hello, world%s\n",
            $in->getOption('yell') ? '!' : '.'
        );
    }
);

option('yell', 'y', OPT_NO_VALUE, 'Yell at user');
```

Rendering Output
----------------

You may also want to use Console's ability to format and color output. To
acquire the output manager, you will need to add a parameter to your task:

```php
<?php

use Symfony\Component\Console\Output\OutputInterface as Out;

task(
    'pretty',
    'Prints a colorful word'.
    function (Out $out) {
        $out->writeln('<info>Pretty!</info>');
    }
);
```

> You may want to read up on what the [Console output][] manager can do.

Advanced Functionality
----------------------

Go is built using

- [Symfony Console][]
- [Herrera.io Cli App][]
- [Herrera.io Service Container][]

Your task may access the service container, which contains the console:

```php
use Herrera\Go\Go;

task(
    'advanced',
    'My advanced task',
    function (Go $go) {
        $console = $go['console'];

        $go->register(new My\ServiceProvider());

        $myService = $go['my_service'];
    }
);
```

You can also create your own or use existing services for your tasks:

```php
use Herrera\Go\Go;

require __DIR__ . '/vendor/autoload.php';

Go::get()->register(new Herrera\Service\Process\ProcessServiceProvider());

task(
    'test',
    'Runs tests',
    function (Go $go) {
        $process = $go['process']('bin/phpunit');

        return $process
            ->arg('--verbose')
            ->arg('--coverage-html')
            ->arg('coverage')
            ->error($process->stream(STDERR))
            ->output($process->stream(STDOUT))
            ->run();
    }
);

```

Putting It All Together
-----------------------

You can mix functionality as needed for each of your tasks. You are not limited
to only using an input, output, or Go instance. You simply need to declare what
you need in your task's parameters:

```php
use Herrera\Go\Go;
use Symfony\Component\Console\Input\InputInterface as In;
use Symfony\Component\Console\Output\OutputInterface as Out;

task(
    'everything',
    'An example of everything',
    function (In $in, Out $out, Go $go) {
    }
);

task(
    'noOrder',
    'Order not matter',
    function (Out $out, Go $go, In $in) {
    }
);
```

> As you may have noticed, the order in which you declare your parameter does
> not matter. Go will perform a type check to determine which value is passed
> to your task.

[Console output]: http://symfony.com/doc/current/components/console/introduction.html#coloring-the-output
[Symfony Console]: http://symfony.com/doc/current/components/console/introduction.html
[Herrera.io Cli App]: https://github.com/herrera-io/php-cli-app
[Herrera.io Service Container]: https://github.com/herrera-io/php-service-container
