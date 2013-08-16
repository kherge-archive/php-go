<?php

namespace Herrera\Go;

use Herrera\Go\Go;
use Herrera\Go\Test\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FunctionsTest extends TestCase
{
    public function getTasks()
    {
        return array(
            array(
                'test',
                'test task',
                function (OutputInterface $output, TestCase $other = null, Go $go) {
                    $output->writeln('success');
                }
            ),
            array(
                'test',
                'test task',
                function (InputInterface $input, $something, OutputInterface $output) {
                    $output->writeln('success');
                }
            ),
            array(
                'test',
                'test task',
                function ($something, OutputInterface $output, InputInterface $input) {
                    $output->writeln('success');
                }
            ),
        );
    }

    public function testArg()
    {
        $this->go['go.last_task'] = $command = $this->go->add(
            'test',
            function () {
            }
        );

        arg('test', ARG_IS_ARRAY, 'test argument', array('default'));

        $def = $command->getDefinition();
        $arg = $def->getArgument('test');

        $this->assertTrue($arg->isArray());
        $this->assertEquals('test argument', $arg->getDescription());
        $this->assertEquals(array('default'), $arg->getDefault());
    }

    public function testOption()
    {
        $this->go['go.last_task'] = $command = $this->go->add(
            'test',
            function () {
            }
        );

        option(
            'test',
            't',
            OPT_IS_ARRAY | OPT_IS_OPTIONAL,
            'test argument',
            array('default')
        );

        $def = $command->getDefinition();
        $opt = $def->getOption('test');

        $this->assertTrue($opt->isArray());
        $this->assertEquals('t', $opt->getShortcut());
        $this->assertEquals('test argument', $opt->getDescription());
        $this->assertEquals(array('default'), $opt->getDefault());
    }

    public function testRun()
    {
        $this
            ->go
            ->add(
                'test',
                function (InputInterface $in) {
                    return (int) $in->getOption('status');
                }
            )
            ->addOption('status', null, InputOption::VALUE_REQUIRED);

        $this->assertEquals(
            123,
            run(
                'test',
                array(
                    '--status' => 123
                )
            )
        );
    }

    public function testTask()
    {
        task(
            'test',
            'test task',
            function () {
            }
        );

        $command = $this->getConsole()->get('test');

        $this->assertEquals('test task', $command->getDescription());
        $this->assertEquals('test task', $command->getHelp());
    }

    /**
     * @depends testTask
     * @dataProvider getTasks
     */
    public function testTaskRun($name, $description, $callable)
    {
        task($name, $description, $callable);

        touch('Gofile');

        $this->setArguments(array('command' => 'test'));

        $status = $this->go->run();

        $this->assertEquals("success\n", $this->getOutput());
        $this->assertSame(0, $status);
    }
}
