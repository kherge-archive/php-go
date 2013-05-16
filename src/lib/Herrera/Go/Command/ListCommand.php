<?php

namespace Herrera\Go\Command;

use Symfony\Component\Console\Command\ListCommand as Base;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates an exception under certain conditions if a tasks file is not
 * present. This allows other aspects of the list command to function even
 * if no tasks file is available.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class ListCommand extends Base
{
    /**
     * @override
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!file_exists('Gofile')) {
            $output->writeln(
                sprintf(
                    '<error>Gofile not present in %s</error>',
                    getcwd()
                )
            );

            return 1;
        }

        return parent::execute($input, $output);
    }
}
