<?php


namespace phpfuncbot\Storage;


use Predis\Async\Client;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;

class ReactRedisTransport implements StorageTransport
{
    /**
     * @var string
     */
    private $uri;
    /**
     * @var LoopInterface
     */
    private $loop;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ReactRedisTransport constructor.
     * @param string $uri
     * @param LoopInterface $loop
     * @param LoggerInterface $logger
     */
    public function __construct(
        string $uri,
        LoopInterface $loop,
        LoggerInterface $logger
    )
    {
        $this->uri = $uri;
        $this->loop = $loop;
        $this->logger = $logger;
    }

    public function connect()
    {
        $client = new Client($this->uri, $this->loop);

        $client->connect(function (Client $client)  {
            $this->logger->debug("REDIS connected: " . json_encode($client->getConnection()->getParameters()->toArray()) . "");

        });

    }


    public function send()
    {

    }

    public function set(string $key, string $value)
    {
        // TODO: Implement set() method.
    }

    public function get(string $key): string
    {
        // TODO: Implement get() method.
    }
}