<?php


namespace phpfuncbot\Telegram;


use phpfuncbot\Queue\Queue;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Http\Response;
use React\Promise\Promise;
use Steelbot\TelegramBotApi\Type\Update;

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
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Queue
     */
    private $queue;

    /**
     * Server constructor.
     * @param string $listen ip:port
     * @param string $path
     * @param Queue $queue
     * @param LoopInterface $loop
     * @param LoggerInterface $logger
     */
    public function __construct(
        string $listen,
        string $path,
        Queue $queue,
        LoopInterface $loop,
        LoggerInterface $logger
    )
    {
        $this->listen = $listen;
        $this->path = $path;
        $this->loop = $loop;
        $this->logger = $logger;
        $this->queue = $queue;
    }


    public function run()
    {
        static $counter = 0;

        $socket = new \React\Socket\Server($this->listen, $this->loop);
        $path = $this->path;

        $server = new \React\Http\Server(function (ServerRequestInterface $request) use ($path, &$counter) {
            $counter++;

            if ($request->getUri()->getPath() !== $path) {
                $this->logger->debug("HTTP <- #{$counter} {$request->getMethod()} {$request->getUri()}; Response 404 (path not matched)");
                return new Response(404);
            }

            if ($request->getMethod() !== 'POST') {
                $this->logger->debug("HTTP <- #{$counter} {$request->getMethod()} {$request->getUri()}; Response 400 (method is not POST)");
                return new Response(400, ['Content-Type' => 'text/plain'], 'POST required');
            }

            if ($request->getHeaderLine('content-type') !== 'application/json') {
                $this->logger->debug("HTTP <- #{$counter} {$request->getMethod()} {$request->getUri()}; Response 400 (content-type is not application/json)");
                return new Response(400, ['Content-Type' => 'text/plain'], 'application/json required');
            }

            return new Promise(function ($resolve, $reject) use ($request, $counter) {

                $body = '';

                $request->getBody()->on('data', function ($data) use (&$body) {
                    $body .= $data;
                });

                $request->getBody()->on('end', function () use ($resolve, &$body, $counter, $request) {

                    $json = @json_decode($body, true);
                    $body = '';
                    if ($json === false || !is_array($json)) {
                        $this->logger->debug("HTTP <- #{$counter} {$request->getMethod()} {$request->getUri()}; Response 400 (json_decode failed)");
                        $resolve(new Response(400, ['Content-Type' => 'text/plain'], "Malformed json"));
                        return;
                    }

                    try {
                        $update = new Update($json);
                    } catch (\Throwable $e) {
                        $this->logger->debug("HTTP <- #{$counter} {$request->getMethod()} {$request->getUri()}; Response 400 (malformed Update object: {$e->getMessage()})");
                        $resolve(new Response(400, ['Content-Type' => 'text/plain'], "Malformed Update object: " . $e->getMessage()));
                        return;
                    }

                    $this->queue->push($update->updateId, $update);

                    $this->logger->info("HTTP <- #{$counter} {$request->getMethod()} {$request->getUri()}; Response 200 (update object #{$update->updateId} registered");
                    $response = new Response(200, ['Content-Type' => 'application/json'], 'true');
                    $resolve($response);
                });

                $request->getBody()->on('error', function (\Exception $e) use ($resolve, &$body, $counter, $request) {
                    $this->logger->debug("HTTP <- #{$counter} {$request->getMethod()} {$request->getUri()}; Response 400 (request decode error: {$e->getMessage()})");
                    $response = new Response(400, ['Content-Type' => 'text/plain'], $e->getMessage());
                    $resolve($response);
                });

            });
        });

        $server->on('error', function (\Exception $e) {
            if ($e->getPrevious() !== null) {
                $e = $e->getPrevious();
            }
            $this->logger->error("HTTP server error: {$e->getMessage()}");
        });

        $server->listen($socket);
        $this->logger->info("HTTP server listen {$socket->getAddress()}, path {$this->path}");
    }
}