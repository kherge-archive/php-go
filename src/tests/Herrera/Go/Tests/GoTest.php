<?php

namespace Herrera\Go\Tests;

use Herrera\Go\Go;
use Herrera\PHPUnit\TestCase;
use Phar;
use Symfony\Component\Console\Command\Command;
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

    public function testCreate()
    {
        $go = Go::create();

        $this->assertInstanceOf('Herrera\\Go\\Go', $go);
    }

    /**
     * @depends test__Invoke
     */
    public function testInvoke()
    {
        $result = null;
        $go = new Go();
        $test = $go(
            'test',
            'Just testing!',
            function ($input) use (&$result) {
                $result = $input->getArgument('what');
            }
        );

        /** @var $test Command */
        $test->addArgument('what');

        $go->invoke('test', array('what' => 'Hello!'));

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

        file_put_contents(
            'Gofile',
            <<<PAKEFILE
<?php

\$task('test', 'Test task', function () use (\$go) {});
PAKEFILE
        );

        $go = new Go();
        $go->load();

        $this->assertInstanceOf(
            'Symfony\\Component\\Console\\Command\\Command',
            $go['console']->find('test')
        );
    }

    /**
     * @depends testCreate
     */
    public function testUpdate()
    {
        $manifest = $this->createFile();

        unlink($current = $this->createFile('go.phar'));
        unlink($update = $this->createFile('go.phar'));

        $phar = new Phar($current);
        $phar->addFromString('test.php', '<?php echo 1;');
        $phar->setStub($phar->createDefaultStub('test.php'));

        unset($phar);

        $phar = new Phar($update);
        $phar->addFromString('test.php', '<?php echo 2;');
        $phar->setStub($phar->createDefaultStub('test.php'));

        unset($phar);

        file_put_contents(
            $manifest,
            json_encode(
                array(
                    array(
                        'name' => 'go.phar',
                        'sha1' => sha1_file($update),
                        'url' => "file://$update",
                        'version' => '1.0.0-alpha.1'
                    ),
                    array(
                        'name' => 'go.phar',
                        'sha1' => sha1_file($update),
                        'url' => "file://$update",
                        'version' => '2.0.0-alpha.1'
                    )
                )
            )
        );

        $_SERVER['argv'][0] = $current;

        $go = Go::create('Test', '1.0.0');
        $go['update.url'] = "file://$manifest";
        $go['console']->setAutoExit(false);
        $go['console']->run(
            new ArrayInput(array('command' => 'update')),
            new NullOutput()
        );

        $this->assertEquals('1', exec('php ' . escapeshellarg($current)));

        $go['console']->run(
            new ArrayInput(
                array(
                    'command' => 'update',
                    '--upgrade' => true,
                    '--pre' => true
                )
            ),
            new NullOutput()
        );

        $this->assertEquals('2', exec('php ' . escapeshellarg($current)));
    }
}
