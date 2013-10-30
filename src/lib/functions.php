<?php

use Herrera\Go\Go;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

if (!defined('ARG_IS_ARRAY')) {

    /**
     * Indicates that the argument may be provided multiple times.
     */
    define('ARG_IS_ARRAY', InputArgument::IS_ARRAY);

    /**
     * Indicates that the argument is optional.
     */
    define('ARG_IS_OPTIONAL', InputArgument::OPTIONAL);

    /**
     * Indicates that the argument is required.
     */
    define('ARG_IS_REQUIRED', InputArgument::REQUIRED);

    /**
     * Indicates that the option may be provided multiple times.
     */
    define('OPT_IS_ARRAY', InputOption::VALUE_IS_ARRAY);

    /**
     * Indicates that the value for the option is optional.
     */
    define('OPT_IS_OPTIONAL', InputOption::VALUE_OPTIONAL);

    /**
     * Indicates that the value for the option is required.
     */
    define('OPT_IS_REQUIRED', InputOption::VALUE_REQUIRED);

    /**
     * Indicates that there is no value for the option.
     */
    define('OPT_NO_VALUE', InputOption::VALUE_NONE);

}

if (!function_exists('arg')) {

    /**
     * Adds a command line argument for the last created task.
     *
     * @param string  $name        The name of the option.
     * @param integer $mode        The option modes.
     * @param string  $description The description of the option.
     * @param mixed   $value       The default value of the option.
     */
    function arg(
        $name,
        $mode = null,
        $description = '',
        $value = null
    ) {
        /** @var $task Command */
        $task = Go::get('go.last_task');

        $task->addArgument($name, $mode, $description, $value);
    }

    /**
     * Adds a command line option for the last created task.
     *
     * @param string  $name        The name of the option.
     * @param string  $shortcut    The shortcut name of the option.
     * @param integer $mode        The option modes.
     * @param string  $description The description of the option.
     * @param mixed   $value       The default value of the option.
     */
    function option(
        $name,
        $shortcut = null,
        $mode = null,
        $description = '',
        $value = null
    ) {
        /** @var $task Command */
        $task = Go::get('go.last_task');

        $task->addOption($name, $shortcut, $mode, $description, $value);
    }

    /**
     * Runs another task.
     *
     * @param string $name      The name of the task.
     * @param array  $arguments The arguments for the task.
     *
     * @return integer The status code.
     */
    function run($name, array $arguments = array())
    {
        /** @var Application $console */
        $console = Go::get('console');

        /** @var OutputInterface $output */
        $output = Go::get('console.output');

        $autoExit = new ReflectionProperty($console, 'autoExit');
        $autoExit->setAccessible(true);

        $autoExit = $autoExit->getValue($console);

        $console->setAutoExit(false);

        $arguments['command'] = $name;

        $status = $console->run(
            new ArrayInput($arguments),
            $output
        );

        $console->setAutoExit($autoExit);

        return $status;
    }

    /**
     * Creates a new task.
     *
     * @param string   $name        The name of the task.
     * @param string   $description The description of the task.
     * @param callable $callable    The callable for the task.
     */
    function task($name, $description, $callable)
    {
        $callable = function (
            InputInterface $input,
            OutputInterface $output
        ) use ($callable) {
            $go = Go::get();
            $func = new ReflectionFunction($callable);
            $params = $func->getParameters();

            foreach ($params as $i => $param) {
                if (null !== ($class = $param->getClass())) {
                    if ($class->isInstance($input)) {
                        $params[$i] = $input;
                    } elseif ($class->isInstance($output)) {
                        $params[$i] = $output;
                    } elseif ($class->isInstance($go)) {
                        $params[$i] = $go;
                    } else {
                        $params[$i] = null;
                    }
                } else {
                    $params[$i] = null;
                }
            }

            return $func->invokeArgs($params);
        };

        Go::get()
            ->add($name, $callable)
            ->setDescription($description)
            ->setHelp($description);
    }

}
