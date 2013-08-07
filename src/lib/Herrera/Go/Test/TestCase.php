<?php

namespace Herrera\Go\Test;

use Herrera\Go\Go;
use Herrera\PHPUnit\TestCase as Base;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * Simplifies testing the Go application.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class TestCase extends Base
{
    /**
     * The temporary working directory.
     *
     * @var string
     */
    protected $dir;

    /**
     * The active Go instance.
     *
     * @var Go
     */
    protected $go;

    /**
     * The current arguments list.
     *
     * @var array
     */
    private $argv;

    /**
     * The original working directory.
     *
     * @var string
     */
    private $cwd;

    /**
     * The console output stream.
     *
     * @var resource
     */
    private $out;

    /**
     * Clears the output stream.
     */
    protected function clearOutput()
    {
        fclose($this->out);

        $this->out = fopen('php://memory', 'w+');

        $this->go['console.output'] = new StreamOutput($this->out);
    }

    /**
     * Returns the current console instance.
     *
     * @return Application The console.
     */
    protected function getConsole()
    {
        /** @var $console \Symfony\Component\Console\Application */
        $console = $this->go['console'];

        return $console;
    }

    /**
     * Returns the output from the output stream.
     *
     * @return string The output.
     */
    protected function getOutput()
    {
        $output = '';

        fseek($this->out, 0, 0);

        while (!feof($this->out)) {
            $output .= fgets($this->out);
        }

        return $output;
    }

    /**
     * Sets the command line arguments.
     */
    protected function setArguments(array $arguments)
    {
        $this->go['console.input'] = new ArrayInput($arguments);
    }

    /**
     * Sets up the test case environment.
     */
    protected function setUp()
    {
        $this->argv = $_SERVER['argv'];
        $this->cwd = getcwd();
        $this->dir = $this->createDir();
        $this->go = new Go();
        $this->out = fopen('php://memory', 'w+');

        chdir($this->dir);

        $this->go['console.input'] = new ArrayInput(array());
        $this->go['console.output'] = new StreamOutput($this->out);

        /** @var $console Application */
        $console = $this->go['console'];
        $console->setAutoExit(false);

        $this->setPropertyValue(
            get_class($this->go),
            'instance',
            $this->go
        );
    }

    /**
     * Tears down the test case environment.
     */
    protected function tearDown()
    {
        fclose($this->out);
        chdir($this->cwd);

        $_SERVER['argv'] = $this->argv;

        parent::tearDown();
    }
}
