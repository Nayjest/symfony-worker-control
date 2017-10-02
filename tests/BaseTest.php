<?php
declare(strict_types=1);

use Nayjest\WorkerControl\WorkerControlCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

abstract class BaseTest extends TestCase
{
    protected $app;
    protected $command;

    public function __construct()
    {
        $this->app = new Application();
        $this->app->add(new WorkerControlCommand());
        $this->command = $this->app->find('worker');
        parent::__construct();
    }

    public function execute(array $params = [])
    {
        $commandTester = (new CommandTester($this->command));
        $commandTester->execute(array_merge(['command' => $this->command->getName()], $params));
        return $commandTester;
    }
}


