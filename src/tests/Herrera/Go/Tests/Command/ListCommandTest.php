<?php

namespace Herrera\Go\Tests\Command;

use Herrera\Go\Go;
use Herrera\Go\Test\TestCase;

class ListCommandTest extends TestCase
{
    public function testExecuteWithTasks()
    {
        touch('Gofile');

        $status = $this->go->run();

        $this->assertRegExp(
            '/Display this help message/',
            $this->getOutput()
        );
        $this->assertSame(0, $status);
    }

    public function testExecuteWithoutTasks()
    {
        $status = $this->go->run();

        $this->assertEquals(
            'Gofile not present in ' . $this->dir . "\n",
            $this->getOutput()
        );
        $this->assertSame(1, $status);
    }
}
