<?php

namespace Herrera\Go\Tests;

use Herrera\Go\Go;
use Herrera\Go\Test\TestCase;
use Phar;

class GoTest extends TestCase
{
    public function testConstruct()
    {
        $console = $this->getConsole();

        $this->assertEquals('Go', $console->getName());
        $this->assertEquals('@git_version@', $console->getVersion());

        $this->assertInstanceOf(
            'Herrera\\Go\\Command\\ListCommand',
            $console->find('list')
        );
    }

    public function testAdd()
    {
        $command = $this->go->add(
            'test',
            function () {
            }
        );

        $this->assertSame($command, $this->go['go.last_task']);
    }

    public function testGetWithKey()
    {
        $this->assertSame($this->go['console'], Go::get('console'));
    }

    public function testGetWithoutKey()
    {
        $this->setPropertyValue(get_class($this->go), 'instance', null);

        $this->assertSame(Go::get(), Go::get());
    }

    public function testRegisterUpdater()
    {
        $this->go->registerUpdater();

        $this->assertTrue(isset($this->go['update']));

        $command = $this->getConsole()->find('update');

        $this->assertNotNull($command);

        $def = $command->getDefinition();

        $this->assertTrue($def->hasOption('pre-release'));
        $this->assertTrue($def->hasOption('upgrade'));
    }

    public function testRegisterUpdaterRun()
    {
        $this->setUpFakeUpdate();

        $this->setArguments(
            array(
                'command' => 'update',
                '--pre-release' => true,
                '--upgrade' => true
            )
        );

        $this->go->registerUpdater();

        $status = $this->go->run();

        $this->assertEquals(
            "Looking for updates...\nUpdated successfully.\n",
            $this->getOutput()
        );
        $this->assertSame(0, $status);

        $this->getConsole()->setVersion('2.0.0');

        $this->clearOutput();

        $status = $this->go->run();

        $this->assertEquals(
            "Looking for updates...\nAlready up-to-date.\n",
            $this->getOutput()
        );
        $this->assertSame(0, $status);
    }

    public function testRunWithTasks()
    {
        file_put_contents(
            'Gofile',
            '<?php task("test", "A test task", function () {});'
        );

        $status = $this->go->run();

        $this->assertRegExp(
            '/A test task/',
            $this->getOutput()
        );
        $this->assertSame(0, $status);
    }

    public function testRunWithoutTasks()
    {
        $this->setArguments(
            array(
                'command' => 'help',
                '--version' => true
            )
        );

        $status = $this->go->run();

        $this->assertEquals(
            "Go version @git_version@\n",
            $this->getOutput()
        );
        $this->assertSame(0, $status);
    }

    private function setUpFakeUpdate()
    {
        touch($_SERVER['argv'][0] = 'test.phar');

        $phar = new Phar('update.phar');
        $phar->addFromString('test.php', 'echo "Hello, world!\n";"');
        $phar->setStub('<?php require "test.php"; __HALT_COMPILER();');

        unset($phar);

        file_put_contents(
            'update.json',
            json_encode(
                array(
                    array(
                        'name' => 'update.phar',
                        'sha1' => sha1_file('update.phar'),
                        'url' => 'update.phar',
                        'version' => '2.0.0'
                    )
                )
            )
        );

        $this->go['update.url'] = 'update.json';

        $this->getConsole()->setVersion('1.0.0');
    }
}
