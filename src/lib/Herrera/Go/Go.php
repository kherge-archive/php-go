<?php

namespace Herrera\Go;

use Herrera\Cli\Application;
use Herrera\Service\Process\ProcessServiceProvider;
use Herrera\Service\Update\UpdateServiceProvider;
use InvalidArgumentException;
use Symfony\Component\Console\Application as Console;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
     * Creates a new, configured instance of Go.
     *
     * @param string $name    The application name.
     * @param string $version The application version.
     *
     * @return Go The new instance.
     */
    public static function create($name = 'Go', $version = '@git_version@')
    {
        $go = new self($name, $version);
        $go->register(new ProcessServiceProvider());

        /** @var $console Console */
        $console = $go['console'];

        if (('@' . 'git_version@') !== $console->getVersion()) {
            $go->register(new UpdateServiceProvider(), array(
                'update.url' => '@manifest_url@'
            ));

            /** @var $update Command */
            $update = $go('update', 'Updates the application.', function (
                InputInterface $input,
                OutputInterface $output
            ) use ($go) {
                $output->writeln('Looking for updates...');

                if ($go['update'](
                    $go['console']->getVersion(),
                    (false === $input->getOption('upgrade')),
                    $input->getOption('pre')
                )){
                    $output->writeln('<info>Update successful!</info>');
                } else {
                    $output->writeln('<comment>Already up-to-date.</comment>');
                }
            });

            $update->addOption(
                'pre',
                'p',
                InputOption::VALUE_NONE,
                'Allow pre-release updates.'
            );

            $update->addOption(
                'upgrade',
                'u',
                InputOption::VALUE_NONE,
                'Upgrade to next major release, if available.'
            );
        }

        return $go;
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

        $go = $this;
        $task = $this;

        include 'Gofile';
    }
}
