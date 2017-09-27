<?php

namespace Nayjest\WorkerControl;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WorkerControlCommand extends BaseCommand
{
    const ACTION_START = 'start';
    const ACTION_RESTART = 'restart';
    const ACTION_STOP = 'stop';
    const ACTION_MAINTAIN = 'maintain';
    /**
     * @var string|null
     */
    protected $defaultCommand;
    /**
     * @var int
     */
    protected $defaultQty;

    /**
     * WorkerControlCommand constructor.
     *
     * @param string $name                command name
     * @param string|null $defaultCommand default command to execute;
     *                                    "cmd" argument will be required if $defaultCommand is null
     * @param int $defaultQty
     */
    public function __construct(
        $name = 'workers',
        $defaultCommand = null,
        $defaultQty = 1
    ) {
        $this->defaultCommand = $defaultCommand;
        $this->defaultQty = $defaultQty;
        parent::__construct($name);
    }

    protected function configure()
    {

        $this
            ->addArgument(
                "action",
                $this->defaultCommand ? InputArgument::OPTIONAL : InputArgument::REQUIRED,
                'start|stop|restart|maintain',
                $this->defaultCommand ? 'maintain' : null
            )
            ->addArgument(
                'cmd',
                $this->defaultCommand ? InputArgument::OPTIONAL : InputArgument::REQUIRED,
                'Worker command',
                $this->defaultCommand
            )
            ->addOption(
                'qty',
                null,
                InputOption::VALUE_REQUIRED,
                'Qty of workers to start/restart/maintain',
                $this->defaultQty
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = @date('Y-m-d H:i:s');
        $action = $input->getArgument('action');
        $cmd = $input->getArgument('cmd');

        $qty = $input->getOption('qty');
        if (!is_numeric($qty) || $qty < 1) {
            throw new InvalidArgumentException("qty must be > 0");
        }

        $service = new Service();
        if ($service->isWindows) {
            $alive = '<unknown qty>';
            if ($action !== self::ACTION_START) {
                throw new RuntimeException("Only 'start' command is supported in Windows");
            }
        } else {
            $alive = $service->getQtyAlive($cmd);
        }

        switch ($action) {
            case self::ACTION_MAINTAIN:
                $needed = $qty - $alive;
                $output->writeln(
                    "[$time] Maintaining $qty workers: $alive alive, $needed to start, command: $cmd"
                );
                $service->start($cmd, $needed);
                break;

            case self::ACTION_STOP:
                $output->writeln(
                    "[$time] Stop workers: $alive alive,  command: $cmd"
                );
                $service->stop($cmd);
                break;

            case self::ACTION_START:
                $output->writeln(
                    "[$time] Start $qty workers:  $alive alive, $qty to start, command: $cmd"
                );
                $service->start($cmd, $qty);
                break;

            case self::ACTION_RESTART:
                $output->writeln(
                    "[$time] Restart processes: stop $alive, start $qty, command: $cmd"
                );
                $service->stop($cmd);
                $service->start($cmd, $qty);
                break;

            default:
                throw new InvalidArgumentException("Invalid action: '$action'");
        }
    }
}
