<?php


namespace phpfuncbot\Storage;


use Predis\Client;
use Predis\Command\ServerFlushDatabase;
use Predis\Command\StringGet;
use Predis\Command\StringSet;
use Psr\Log\LoggerInterface;

/**
 * Class RedisTransport
 */
class RedisTransport implements StorageTransport
{
    /**
     * @var string
     */
    private $uri;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Client
     */
    private $redis;

    public function __construct(string $uri, LoggerInterface $logger)
    {
        $this->uri = $uri;
        $this->logger = $logger;
        $this->connect();
    }

    private function connect()
    {
        $this->redis = $client = new Client($this->uri);
        $client->connect();
        $this->logger->debug("REDIS connected to {$this->uri}");
    }

    public function set(string $key, string $value)
    {
        $command = new StringSet();
        $command->setArguments([$key, $value]);
        $return =  $this->redis->executeCommand($command);
        $this->logger->debug("REDIS SET {$key} {$value} // Response: {$return}");
        return $return;
    }

    public function get(string $key): string
    {
        $command = new StringGet();
        $command->setArguments([$key]);
        $return = $this->redis->executeCommand($command);
        $this->logger->debug("REDIS GET {$key}: {$return}");
        return $return;
    }

    public function flushdb()
    {
        $command = new ServerFlushDatabase();
        $return = $this->redis->executeCommand($command);
        $this->logger->debug("REDIS FLUSHDB // Response: {$return}");
        return $return;
    }

}