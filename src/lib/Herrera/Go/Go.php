<?php

namespace Herrera\Go;

use Herrera\Cli\Application;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Creates commands from tasks listed in a Pakefile.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Go extends Application
{
    /**
     * Registers a task as a command.
     *
     * @param string   $name        The name.
     * @param string   $description The description.
     * @param callable $callback    The callback.
     *
     * @return Command The task command.
     */
    public function __invoke($name, $description, $callback)
    {
        return $this->add($name, $callback)
                    ->setDescription($description)
                    ->setHelp($description);
    }

    /**
     * Invokes a task command.
     *
     * @param string $name The name.
     * @param array  $args The arguments.
     *
     * @return integer The exit status code.
     */
    public function invoke($name, array $args = array())
    {
        $args['command'] = $name;

        return $this['console']->find($name)->run(
            new ArrayInput($args),
            $this['console.output']
        );
    }

    /**
     * Loads the task file.
     */
    public function load()
    {
        if (false === file_exists('Gofile')) {
            throw new InvalidArgumentException('No Gofile available.');
        }

        $pake = $this;
        $task = $this;

        include 'Gofile';
    }
}