<?php

namespace Herrera\Pake\Tests;

use Herrera\Pake\Pake;
use Herrera\PHPUnit\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class PakeTest extends TestCase
{
    public function testInvoke()
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
        $pake->load('Pakefile');

        $this->assertInstanceOf(
            'Symfony\\Component\\Console\\Command\\Command',
            $pake['console']->find('test')
        );
    }
}