Go
====

[![Build Status](https://travis-ci.org/herrera-io/php-go.png?branch=master)](https://travis-ci.org/herrera-io/php-go)

A simple PHP build tool.

Summary
-------

Go is a simple build tool inspired by [Cake](http://coffeescript.org/documentation/docs/cake.html).

Installation
------------

You may add Go as a Composer dependency:

```sh
$ php composer.phar require herrera-io/go=1.*
```

And run it from your bin directory:

```sh
$ bin/go
```

Or you may download it [as a PHAR](https://bitbucket.org/kherge/php-go/downloads/).

Usage
-----

To use Go, you will need to create a Gofile:

```php
<?php

$task('hello', 'Just want to say hello!', function ($input, $output) {
    $output->writeln(sprintf(
        'Hello, <info>%s</info>!',
        $input->getArgument('name') ?: 'Guest'
    ));
})->addArgument('name');

$task('test', 'Test using PHPUnit', function () use ($go) {
    $process = $go['process']('bin/phpunit');

    return $process->error($process->stream(STDERR))
                   ->output($process->stream(STDOUT))
                   ->run();
});
```

You may then either list the available tasks by running Go without arguments,

```sh
$ bin/go
Go version 1.0.0

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
$ bin/go help hello
Usage:
 hello [name]

Arguments:
 name

Help:
 Just want to say hello!

```

or run a particular task.

```sh
$ bin/go hello
Hello, Guest!
$ bin/go hello User
Hello, User!
```
