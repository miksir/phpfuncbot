<?php

namespace phpfuncbot\Logger;


class ConsoleLogger extends AbstractLogger
{
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        if ($this->isPassMessage($level)) {
            echo "\"{$level}\" {$message}\n";
        }
    }
}