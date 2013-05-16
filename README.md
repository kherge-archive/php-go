Go
==

[![Build Status]](http://travis-ci.org/herrera-io/php-go)

Go is a simple PHP build tool built on [Symfony Console][].

**Gofile**:

```php
<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// create our task
task(
    'hello',
    'Say hello',
    function (InputInterface $input, OutputInterface $output) {
        $output->writeln(
            sprintf(
                'Hello, %s%s',
                $input->getArgument('name'),
                $input->getOption('ending')
            )
        );
    }
);

// add an argument to the task
arg('name', ARG_IS_OPTIONAL, 'Your name', 'world');

// add an option to the task
option('ending', 'e', OPT_IS_OPTIONAL, 'How to end', '!');
```

```sh
$ bin/go hello
Hello, world!
$ bin/go hello Kevin -e .
Hello, Kevin.
```

Documentation
-------------

- [Installing][]
- [Usage][]

[Build Status]: https://secure.travis-ci.org/herrera-io/php-go.png?branch=master
[Symfony Console]: http://symfony.com/doc/current/components/console/introduction.html
[Installing]: doc/00-Installing.md
[Usage]: doc/01-Usage.md
