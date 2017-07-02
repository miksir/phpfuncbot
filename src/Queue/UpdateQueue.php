<?php


namespace phpfuncbot\Queue;


use Psr\Log\LoggerInterface;

class UpdateQueue implements Queue
{
    static $counter = 0;
    static $queue = [];
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * UpdateQueue constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
    }

    public function push($id, $message)
    {
        $counter = ++static::$counter;
        static::$queue[$id] = $message;
        $this->logger->debug("Queue push #{$counter} ID: {$id}; Message: " . json_encode($message, JSON_UNESCAPED_UNICODE));
    }
}