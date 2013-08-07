<?php

namespace Herrera\Go;

use Herrera\Cli\Application;
use Herrera\Go\Command\ListCommand;
use Herrera\Service\Update\UpdateServiceProvider;
use Symfony\Component\Console\Application as Console;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates commands from tasks listed in a Gofile.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Go extends Application
{
    /**
     * The application singleton.
     *
     * @var Go
     */
    private static $instance;

    /**
     * @override
     */
    public function __construct(array $container = array())
    {
        parent::__construct(
            array_merge(
                array(
                    'app.name' => 'Go',
                    'app.version' => '@git_version@'
                ),
                $container
            )
        );

        /** @var $console Console */
        $console = $this['console'];
        $console->add(new ListCommand());
    }

    /**
     * @override
     */
    public function add($name, $callback)
    {
        return $this['go.last_task'] = parent::add($name, $callback);
    }

    /**
     * Returns the application singleton.
     *
     * @param string $key A service or parameter key.
     *
     * @return Go The application singleton.
     */
    public static function get($key = null)
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return $key ? self::$instance[$key] : self::$instance;
    }

    /**
     * Allows the application to be updated.
     *
     * @return Go The application.
     */
    public function registerUpdater()
    {
        $this->register(
            new UpdateServiceProvider(),
            array('update_url' => '@manifest_url@')
        );

        $app = $this;
        $command = $this->add(
            'update',
            function (
                InputInterface $input,
                OutputInterface $output
            ) use ($app) {
                $output->writeln('Looking for updates...');

                /** @var Console $updated */
                $console = $app['console'];
                $updated = $app['update'](
                    $console->getVersion(),
                    !$input->getOption('upgrade'),
                    $input->getOption('pre-release')
                );

                if ($updated) {
                    $output->writeln('<info>Updated successfully.</info>');
                } else {
                    $output->writeln('<comment>Already up-to-date.</comment>');
                }
            }
        );

        $command->addOption(
            'pre-release',
            'p',
            InputOption::VALUE_NONE,
            'Allow pre-release updates.'
        );

        $command->addOption(
            'upgrade',
            'u',
            InputOption::VALUE_NONE,
            'Allow upgrade to next major release.'
        );

        return $this;
    }

    /**
     * @override
     */
    public function run()
    {
        /** @var $input ArgvInput */
        $input = $this['console.input'];

        switch ($input->getFirstArgument()) {
            case 'help':
            case 'list':
                break;
            default:
                if (file_exists('Gofile')) {
                    require 'Gofile';
                }
        }

        return parent::run();
    }
}
