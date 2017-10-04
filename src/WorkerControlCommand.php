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
    const ACTION_COUNT = 'count';
    const ACTION_LIST = 'list';

    /** @var string|null */
    protected $defaultCommand;

    /** @var int */
    protected $defaultQty;

    /** @var Service */
    protected $service;

    /**
     * WorkerControlCommand constructor.
     *
     * @param string|null $defaultCommand default command to execute;
     *                                    "cmd" argument will be required if $defaultCommand is null
     * @param int $defaultQty
     * @param string|null $workingDir
     */
    public function __construct(
        $defaultCommand = null,
        $defaultQty = 1,
        $workingDir = null
    ) {
        $this->defaultCommand = $defaultCommand;
        $this->defaultQty = $defaultQty;
        $this->service = new Service($workingDir);
        parent::__construct('workers');
    }

    protected function configure()
    {
        $this
            ->addArgument(
                'action',
                $this->defaultCommand ? InputArgument::OPTIONAL : InputArgument::REQUIRED,
                'start|stop|restart|maintain|count',
                $this->defaultCommand ? 'maintain' : null
            )
            ->addArgument(
                'cmd',
                $this->defaultCommand ? InputArgument::OPTIONAL : InputArgument::REQUIRED,
                'Worker command',
                $this->defaultCommand ?: null
            )
            ->addOption(
                'qty',
                null,
                InputOption::VALUE_REQUIRED,
                'Qty of workers to start/restart/maintain',
                $this->defaultQty
            )
            ->addOption(
                'output',
                'o',
                InputOption::VALUE_REQUIRED,
                'Output for workers',
                $this->service->isWindows ? 'NUL' : '/dev/null'
            )
            ->addOption(
                'errors',
                'e',
                InputOption::VALUE_REQUIRED,
                'Error output for workers (--output value is used by default)',
                null
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time = @date('Y-m-d H:i:s');
        $time = "[<fg=yellow>$time]</>";
        $action = $input->getArgument('action');

        $cmd = $input->getArgument('cmd');

        $qty = $input->getOption('qty');
        if (!is_numeric($qty) || $qty < 1) {
            throw new InvalidArgumentException("qty must be > 0");
        }

        if ($this->service->isWindows) {
            $alive = '<unknown qty>';
            if ($action !== self::ACTION_START) {
                throw new RuntimeException("Only 'start' command is supported in Windows");
            }
        } else {
            $alive = $this->service->getQtyAlive($cmd, [$this->getName()]);
        }

        switch ($action) {
            case self::ACTION_COUNT:
                echo "$alive\n";
                return;

            case self::ACTION_LIST:
                $output->write(
                    $this->service->getList(
                        $cmd,
                        [$this->getName()]
                    )
                );
                return;

            case self::ACTION_MAINTAIN:
                $needed = $qty - $alive;
                if ($needed < 0) {
                    $needed = 0;
                }
                $output->writeln(
                    "$time Maintaining $qty workers: "
                    . "<fg=green>$alive alive</>, <fg=cyan>$needed to start</>, command: $cmd"
                );
                $this->start($needed, $input, $output);
                break;

            case self::ACTION_STOP:
                $output->writeln(
                    "$time <fg=red>Stop $alive workers</>: $cmd"
                );
                $this->service->stop($cmd);
                break;

            case self::ACTION_START:
                $aliveMsg = $alive ? " in addition to <fg=green>$alive alive</>" : "";
                $output->writeln(
                    "$time <fg=cyan>Starting $qty</> workers{$aliveMsg}, command: $cmd"
                );
                $this->start($qty, $input, $output);
                break;

            case self::ACTION_RESTART:
                $output->writeln(
                    "$time Restarting workers: <fg=red>stop $alive</>, <fg=cyan>start $qty</>, command: $cmd"
                );
                $this->service->stop($cmd);
                $this->start($qty, $input, $output);
                break;

            default:
                throw new InvalidArgumentException("Invalid action: '$action'");
        }
        $output->writeln(
            "$time Done."
        );
    }

    protected function start($qty, InputInterface $input, OutputInterface $output)
    {
        $outputFile = $input->getOption('output');
        $errorsOutputFile = $input->getOption('errors');
        if (!$errorsOutputFile) {
            $errorsOutputFile = $outputFile;
        }

        $executed = $this->service->start(
            $input->getArgument('cmd'),
            $qty,
            $outputFile,
            $errorsOutputFile
        );
        foreach ($executed as $command) {
            $output->writeln(
                "- Executed: <fg=cyan>$command</>"
            );
        }
    }
}
