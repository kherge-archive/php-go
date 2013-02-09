<?php

namespace Herrera\Pake\Tests;

use Herrera\Pake\Pake;
use Herrera\PHPUnit\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class PakeTest extends TestCase
{
    public function test__Invoke()
    {
        $pake = new Pake();
        $ran = false;

        $pake('test', 'Testing', function () use (&$ran) {
            $ran = true;
        });

        $command = $pake['console']->find('test');

        $this->assertInstanceOf(
            'Symfony\\Component\\Console\\Command\\Command',
            $command
        );
        $this->assertEquals('test', $command->getName());
        $this->assertEquals('Testing', $command->getDescription());

        $command->run(new ArrayInput(array('test')), new NullOutput());

        $this->assertTrue($ran);
    }

    /**
     * @depends test__Invoke
     */
    public function testInvoke()
    {
        $result = null;
        $pake = new Pake();

        $pake('test', 'Just testing!', function ($input, $output) use (&$result) {
            $result = $input->getArgument('what');
        })->addArgument('what');

        $pake->invoke('test', array(
            'what' => 'Hello!'
        ));

        $this->assertEquals('Hello!', $result);
    }

    public function testLoadNoPakefile()
    {
        chdir($this->createDir());

        $pake = new Pake();

        $this->setExpectedException(
            'InvalidArgumentException',
            'No Pakefile available.'
        );

        $pake->load('Pakefile');
    }

    public function testLoad()
    {
        chdir($this->createDir());

        file_put_contents('Pakefile', <<<PAKEFILE
<?php

\$task('test', 'Test task', function () {});
PAKEFILE
        );

        $pake = new Pake();
        $pake->load();

        $this->assertInstanceOf(
            'Symfony\\Component\\Console\\Command\\Command',
            $pake['console']->find('test')
        );
    }
}