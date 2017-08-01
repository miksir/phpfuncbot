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
        static $counter = 0;
        $counter++;

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
            $this->logger->info("HTTP -> #{$counter} POST {$url}; json: {$json}");

        } elseif ($abstractMethod->getHttpMethod() === 'GET') {
            $queryString = build_query($abstractMethod->getParams());
            $request = $this->client->request(
                $abstractMethod->getHttpMethod(),
                $url . ($queryString ? "?{$queryString}" : '')
            );
            $this->logger->info("HTTP -> #{$counter} GET {$url}?{$queryString}");

        } else {
            throw new \InvalidArgumentException("Unknown HTTP method {$abstractMethod->getHttpMethod()} in {$abstractMethod->getMethodName()}");
        }


        $request->on('error', function (\Exception $error) use ($deferred) {
            $deferred->reject($error);
        });

        $request->on('response', function (Response $response) use ($deferred, $abstractMethod, $counter) {
            $buffer = '';

            $response->on('data', function ($chunk) use (&$buffer) {
                $buffer .= $chunk;
            });

            $response->on('end', function() use ($deferred, &$buffer, $abstractMethod, $counter) {
                $this->logger->debug("HTTP -> #{$counter} response: {$buffer}");

                $answer = json_decode($buffer, true);
                $buffer = null;

                if ($answer['ok'] === false) {
                    $exception = new TelegramBotApiException($answer['description'], $answer['error_code']);

                    if (isset($answer['parameters'])) {
                        $parameters = new ResponseParameters($answer['parameters']);
                        $exception->setParameters($parameters);
                    }

                    $deferred->reject($exception);
                    return;
                }

                $answer = $abstractMethod->buildResult($answer['result'] ?? []);
                $deferred->resolve($answer);
            });
        });

        $request->end($json);

        return $deferred->promise();
    }

}