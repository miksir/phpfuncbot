<?php


namespace phpfuncbot\Telegram;


use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\LoopInterface;

class Server
{
    /**
     * @var string
     */
    private $listen;
    /**
     * @var string
     */
    private $path;
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * Server constructor.
     * @param string $listen ip:port
     * @param string $path
     * @param LoopInterface $loop
     */
    public function __construct(
        string $listen,
        string $path,
        LoopInterface $loop
    )
    {
        $this->listen = $listen;
        $this->path = $path;
        $this->loop = $loop;
    }


    public function run()
    {
        $socket = new \React\Socket\Server($this->listen, $this->loop);
        $path = $this->path;
        $server = new \React\Http\Server(function (ServerRequestInterface $request) use ($path) {
            if ($request->getUri()->getPath() !== $path) {
                return new \React\Http\Response(404);
            }

            $json = $request->getParsedBody();
        });
        $server->listen($socket);
    }
}