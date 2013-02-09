Pake
====

[![Build Status](https://travis-ci.org/herrera-io/php-pake.png?branch=master)](https://travis-ci.org/herrera-io/php-pake)

A simple PHP build tool.

Summary
-------

Pake is a simple build tool inspired by [Cake](http://coffeescript.org/documentation/docs/cake.html).

Installation
------------

You may add Pake as a Composer dependency:

```sh
$ php composer.phar require herrera-io/pake=1.*
```

And run it from your bin directory:

```sh
$ bin/pake
```

Or you may download it [as a PHAR](https://bitbucket.org/kherge/php-pake/downloads/pake-1.1.1.phar).

Usage
-----

To use Pake, you will need to create a Pakefile:

```php
<?php

$task('hello', 'Just want to say hello!', function ($input, $output) {
    $output->writeln(sprintf(
        'Hello, <info>%s</info>!',
        $input->getArgument('name') ?: 'Guest'
    ));
})->addArgument('name');

$task('test', 'Test using PHPUnit', function () use ($pake) {
    $process = $pake['process']('bin/phpunit');

    return $process->error($process->stream(STDERR))
                   ->output($process->stream(STDOUT))
                   ->run();
});
```

You may then either list the available tasks by running Pake without arguments,

```sh
$ bin/pake
Pake version 1.0.0

Usage:
  [options] command [arguments]

Options:
  --help           -h Display this help message.
  --quiet          -q Do not output any message.
  --verbose        -v Increase verbosity of messages.
  --version        -V Display this application version.
  --ansi              Force ANSI output.
  --no-ansi           Disable ANSI output.
  --no-interaction -n Do not ask any interactive question.

Available commands:
  hello   Just want to say hello!
  help    Displays help for a command
  list    Lists commands
  test    Test using PHPUnit
```

display the help screen for a particular task,

```sh
$ bin/pake help hello
Usage:
 hello [name]

Arguments:
 name

Help:
 Just want to say hello!

```

or run a particular task.

```sh
$ bin/pake hello
Hello, Guest!
$ bin/pake hello User
Hello, User!
```
