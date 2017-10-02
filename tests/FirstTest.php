<?php
declare(strict_types=1);

/**
 * Simple Class for simple base tests
 * Class FirstTest
 *
 */
class FirstTest extends BaseTest
{
    private $cmd = "php tests/example-worker.php";

    public function testStart()
    {
        $this->execute(['action' => 'stop', 'cmd' => $this->cmd]);
        $commandTester = $this->execute(['action' => 'start', 'cmd' => $this->cmd]);
        $this->assertContains("Starting 1 workers, command: {$this->cmd}", $commandTester->getDisplay());
        $this->execute(['action' => 'stop', 'cmd' => $this->cmd]);
    }

    public function testMaintain()
    {
        $this->execute(['action' => 'stop', 'cmd' => $this->cmd]);
        $commandTester = $this->execute(['action' => 'maintain', 'cmd' => $this->cmd]);
        $this->assertContains("Maintaining 1 workers: 0 alive, 1 to start, command: {$this->cmd}", $commandTester->getDisplay());
        $this->execute(['action' => 'stop', 'cmd' => $this->cmd]);
    }

    public function testStop()
    {
        $this->execute(['action' => 'stop', 'cmd' => $this->cmd]);
        $this->execute(['action' => 'maintain', 'cmd' => $this->cmd]);
        $commandTester = $this->execute(['action' => 'stop', 'cmd' => $this->cmd]);
        $this->assertContains("Stop 1 workers: {$this->cmd}", $commandTester->getDisplay());
    }

    public function testRestart()
    {
        $this->execute(['action' => 'stop', 'cmd' => $this->cmd]);
        $commandTester = $this->execute(['action' => 'restart', 'cmd' => $this->cmd]);
        $this->assertContains("Restarting workers: stop 0, start 1, command: {$this->cmd}", $commandTester->getDisplay());
        $commandTester = $this->execute(['action' => 'restart', 'cmd' => $this->cmd]);
        $this->assertContains("Restarting workers: stop 1, start 1, command: {$this->cmd}", $commandTester->getDisplay());
        $this->execute(['action' => 'stop', 'cmd' => $this->cmd]);
    }

    public function testCount()
    {
        $count = rand(1, 5);
        $this->execute(['action' => 'stop', 'cmd' => $this->cmd]);
        $this->execute(['action' => 'maintain', 'cmd' => $this->cmd, '--qty' => $count]);
        $commandTester = $this->execute(['action' => 'count', 'cmd' => $this->cmd]);
        $this->assertContains("Current workers count: {$count}", $commandTester->getDisplay());
        $this->execute(['action' => 'stop', 'cmd' => $this->cmd]);
    }
    public function testList()
    {
        $this->execute(['action' => 'stop', 'cmd' => $this->cmd]);
        $this->execute(['action' => 'maintain', 'cmd' => $this->cmd, '--qty' => 1]);
        $commandTester = $this->execute(['action' => 'list', 'cmd' => $this->cmd]);
        $this->assertContains('php tests/example-worker.php', $commandTester->getDisplay());
        $this->execute(['action' => 'stop', 'cmd' => $this->cmd]);
    }

    public function testWrongAction()
    {
        $this->execute(['action' => 'stop', 'cmd' => $this->cmd]);
        $this->expectException(InvalidArgumentException::class);
        $this->execute(['action' => 'wrong', 'cmd' => $this->cmd]);
    }

    public function testWrongQTY()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->execute(['action' => 'stop', 'cmd' => $this->cmd]);
        $this->execute(['action' => 'maintain', 'cmd' => $this->cmd, '--qty' => -1]);
    }

    public function testLessMaintainQTY()
    {
        $this->execute(['action' => 'stop', 'cmd' => $this->cmd]);
        $this->execute(['action' => 'start', 'cmd' => $this->cmd, '--qty' => 5]);
        $commandTester = $this->execute(['action' => 'maintain', 'cmd' => $this->cmd, '--qty' => 2]);
        $this->assertContains("Maintaining 2 workers: 5 alive, 0 to start, command: {$this->cmd}", $commandTester->getDisplay());
        $this->execute(['action' => 'stop', 'cmd' => $this->cmd]);
    }
}