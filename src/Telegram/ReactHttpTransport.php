<?php


namespace phpfuncbot\Telegram;


use function GuzzleHttp\Psr7\build_query;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\HttpClient\Request;
use React\HttpClient\Response;
use React\HttpClient\Client;
use React\Promise\Deferred;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use Steelbot\TelegramBotApi\Exception\TelegramBotApiException;
use Steelbot\TelegramBotApi\Method\AbstractMethod;
use Steelbot\TelegramBotApi\Type\ResponseParameters;

class ReactHttpTransport implements Transport
{
    /**
     * Telegram API entry point
     * @var string
     */
    protected $url = 'https://api.telegram.org/bot';
    /**
     * @var string
     */
    private $key;
    /**
     * @var Client
     */
    private $client;
    /**
     * @var LoopInterface
     */
    private $loop;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ReactHttpTransport constructor.
     * @param string $key
     * @param LoopInterface $loop
     * @param LoggerInterface $logger
     */
    public function __construct(string $key, LoopInterface $loop, LoggerInterface $logger)
    {
        $this->key = $key;
        $this->client = new Client($loop);
        $this->loop = $loop;
        $this->logger = $logger;
    }

    public function send(AbstractMethod $abstractMethod) : PromiseInterface
    {
        $deferred = new Deferred();

        $json = null;
        $url = $this->url . $this->key . '/' . $abstractMethod->getMethodName();

        if ($abstractMethod->getHttpMethod() === "POST") {
            $json = json_encode($abstractMethod, JSON_UNESCAPED_UNICODE);
            $request = $this->client->request(
                $abstractMethod->getHttpMethod(),
                $url,
                [
                    'Content-Type' => 'application/json',
                    'Content-Length' => strlen($json)
                ]
            );
            $this->logger->debug("Outgoing POST {$url}, json: {$json}");
        } else {
            $queryString = build_query($abstractMethod->getParams());
            $request = $this->client->request(
                $abstractMethod->getHttpMethod(),
                $url . ($queryString ? "?{$queryString}" : '')
            );
            $this->logger->debug("Outgoing GET {$url}?{$queryString}");
        }

        $request->on('error', function (\Exception $error) use ($deferred) {
            $deferred->reject($error);
        });

        $request->on('response', function (Response $response) use ($deferred, $abstractMethod) {
            $buffer = '';

            $response->on('data', function ($chunk) use (&$buffer) {
                $buffer .= $chunk;
            });

            $response->on('end', function() use ($deferred, &$buffer, $abstractMethod) {
                $this->logger->debug("-> response: {$buffer}");

                $answer = json_decode($buffer, true);

                if ($answer['ok'] === false) {
                    $exception = new TelegramBotApiException($answer['description'], $answer['error_code']);

                    if (isset($answer['parameters'])) {
                        $parameters = new ResponseParameters($answer['parameters']);
                        $exception->setParameters($parameters);
                    }

                    $deferred->reject($exception);
                }

                $buffer = null;
                print_r($answer);
                $answer = $abstractMethod->buildResult($answer['result'] ?? []);
                $deferred->resolve($answer);
            });
        });

        $request->end($json);

        return $deferred->promise();
    }

}