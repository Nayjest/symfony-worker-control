<?php

namespace Nayjest\WorkerControl;

use RuntimeException;

/**
 * Class WorkerControl
 *
 * @internal
 */
class Service
{
    public $isWindows;
    /**
     * @var string
     */
    private $phpInterpreter;

    public function __construct($phpInterpreter = 'php')
    {
        $this->isWindows = (substr(php_uname(), 0, 7) === 'Windows');
        $this->phpInterpreter = $phpInterpreter;
    }

    /**
     * Starts worker processes.
     *
     * @param string $cmd
     * @param int $qty
     * @param string|null $log
     * @param string| null $errLog
     * @return array
     */
    public function start($cmd, $qty, $log = null, $errLog = null)
    {
        $cmd = $this->prepare($cmd);
        $cmd = $this->redirectOutput($cmd, $log, $errLog);
        $executed = [];
        for ($i = 1; $i <= $qty; $i++) {
            if ($this->isWindows) {
                pclose(popen("start $cmd", 'r'));
            } else {
                $final_cmd = str_replace('{i}', $i, $cmd) . ' &';
                exec($final_cmd);
                $executed[] = $final_cmd;
            }
        }
        return $executed;
    }

    /**
     * Returns command with output redirection.
     *
     * @param string $cmd
     * @param string|null $output
     * @param string|null $errorsOutput
     * @return string
     */
    public function redirectOutput($cmd, $output = null, $errorsOutput = null)
    {
        if ($errorsOutput && $output && $errorsOutput === $output) {
            return "$cmd >> $output 2>&1";
        }
        if ($errorsOutput) {
            $cmd = "$cmd 2>> $errorsOutput";
        }
        if ($output) {
            $cmd = "$cmd 1>> $output";
        }
        return $cmd;
    }

    /**
     * Kills all worker processes.
     *
     * @param string $cmd
     */
    public function stop($cmd)
    {
        $cmd = $this->prepare($cmd);
        if ($this->isWindows) {
            throw new RuntimeException("'Stop' isn't supported in Windows");
        }
        `pkill -f "$cmd"`;
    }

    /**
     * Returns quantity of alive worker processes.
     *
     * @param string $cmd
     * @return int
     */
    public function getQtyAlive($cmd, array $except = [])
    {
        $cmd = $this->prepare($cmd);
        if ($this->isWindows) {
            throw new RuntimeException(
                "Receiving qty of running processes isn't supported in Windows"
            );
        }
        # (int)trim(`pgrep -f -c "$cmd"`);

        $output = trim($this->getList($cmd, $except));
        if ($output === '') {
            return 0;
        }
        return count(explode(PHP_EOL, $output));
    }

    /**
     * Returns list of alive worker processes.
     *
     * @param string $cmd
     * @param array $except
     * @return string
     */
    public function getList($cmd, array $except = [])
    {
        $cmd = $this->prepare($cmd);
        $output = `pgrep -a -f "$cmd"`;
        return $this->filterList($output, array_merge($except, ['pgrep']));
    }

    protected function filterList($output, array $except)
    {
        $lines = explode("\n", $output);
        foreach ($lines as $i => $line) {
            foreach ($except as $ex) {
                if (strpos($line, $ex) !== false) {
                    unset($lines[$i]);
                    break;
                }
            }
        }
        return join("\n", $lines);
    }

    protected function prepare($cmd)
    {
        if (substr($cmd, -4, 4) === '.php'
            && strpos($cmd, "php ") === false
            && strpos($cmd, "$this->phpInterpreter ") === false
        ) {
            return "$this->phpInterpreter $cmd";
        }
        return $cmd;
    }
}
