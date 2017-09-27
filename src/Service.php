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

    public function __construct()
    {
        $this->isWindows = (substr(php_uname(), 0, 7) === "Windows");
    }

    /**
     * @param string $cmd
     * @param int $qty
     */
    public function start($cmd, $qty)
    {

        for ($i = 1; $i <= $qty; $i++) {
            if ($this->isWindows) {
                pclose(popen("start " . $cmd, "r"));
            } else {
                exec($cmd . ' &');
            }
        }
    }

    /**
     * @param string $cmd
     */
    public function stop($cmd)
    {
        if ($this->isWindows) {
            throw new RuntimeException("'Stop' isn't supported in Windows");
        }
        `pkill -f "$cmd"`;
    }

    /**
     * @param string $cmd
     * @return int
     */
    public function getQtyAlive($cmd)
    {
        if ($this->isWindows) {
            throw new RuntimeException(
                "Receiving qty of running processes isn't supported in Windows"
            );
        }
        return (int)trim(`pgrep -f -c "$cmd"`);
    }
}
