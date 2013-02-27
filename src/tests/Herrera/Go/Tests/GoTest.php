<?php

namespace Herrera\Go\Tests;

use Herrera\Go\Go;
use Herrera\PHPUnit\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class GoTest extends TestCase
{
    public function test__Invoke()
    {
        $go = new Go();
        $ran = false;

        $go('test', 'Testing', function () use (&$ran) {
            $ran = true;
        });

        $command = $go['console']->find('test');

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
        $go = new Go();

        $go('test', 'Just testing!', function ($input, $output) use (&$result) {
            $result = $input->getArgument('what');
        })->addArgument('what');

        $go->invoke('test', array(
            'what' => 'Hello!'
        ));

        $this->assertEquals('Hello!', $result);
    }

    public function testLoadNoGofile()
    {
        chdir($this->createDir());

        $go = new Go();

        $this->setExpectedException(
            'InvalidArgumentException',
            'No Gofile available.'
        );

        $go->load('Gofile');
    }

    public function testLoad()
    {
        chdir($this->createDir());

        file_put_contents('Gofile', <<<PAKEFILE
<?php

\$task('test', 'Test task', function () {});
PAKEFILE
        );

        $go = new Go();
        $go->load();

        $this->assertInstanceOf(
            'Symfony\\Component\\Console\\Command\\Command',
            $go['console']->find('test')
        );
    }
}