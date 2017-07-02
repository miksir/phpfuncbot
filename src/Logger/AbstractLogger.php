<?php


namespace phpfuncbot\Logger;


use Psr\Log\LogLevel;

abstract class AbstractLogger extends \Psr\Log\AbstractLogger
{
    protected $levelsOrder = [LogLevel::EMERGENCY, LogLevel::ALERT, LogLevel::CRITICAL, LogLevel::ERROR, LogLevel::WARNING, LogLevel::NOTICE, LogLevel::INFO, LogLevel::DEBUG];

    protected $logLevel = false;

    /**
     * @param string $logLevel
     */
    public function setLogLevel(string $logLevel)
    {
        $this->logLevel = array_search($logLevel, $this->levelsOrder);
    }

    protected function isPassMessage(string $logLevel)
    {
        if ($this->logLevel === false) {
            return true;
        }

        $askedLevel = array_search($logLevel, $this->levelsOrder);

        return ($askedLevel <= $this->logLevel);
    }
}