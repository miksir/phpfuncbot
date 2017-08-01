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
        $httpMethod = $abstractMethod->getHttpMethod();
        $queryString = build_query($abstractMethod->getParams());
        $url .= ($queryString ? '?'.$queryString : '');

        $logUrl = $this->url . '***' . '/' . $abstractMethod->getMethodName() . ($queryString ? '?'.$queryString : '');

        if ($httpMethod === "POST") {

            $json = json_encode($abstractMethod, JSON_UNESCAPED_UNICODE);
            $request = $this->client->request(
                $httpMethod,
                $url,
                [
                    'Content-Type' => 'application/json',
                    'Content-Length' => strlen($json)
                ]
            );
            $this->logger->debug("HTTP -> #{$counter} {$httpMethod} {$logUrl}; json: {$json}");

        } elseif ($httpMethod === 'GET') {

            $request = $this->client->request(
                $httpMethod,
                $url
            );
            $this->logger->debug("HTTP -> #{$counter} {$httpMethod} {$logUrl}");

        } else {

            throw new \InvalidArgumentException("Unknown HTTP method {$httpMethod} in {$abstractMethod->getMethodName()}");

        }

        $request->on('error', function (\Exception $error) use ($deferred, $counter, $httpMethod, $logUrl) {
            $this->logger->error("HTTP -> #{$counter} {$httpMethod} {$logUrl} Request error: {$error->getMessage()}");
            $deferred->reject($error);
        });

        $request->on('response', function (Response $response) use ($deferred, $abstractMethod, $counter, $httpMethod, $logUrl) {
            $buffer = '';

            $response->on('data', function ($chunk) use (&$buffer) {
                $buffer .= $chunk;
            });

            $response->on('end', function() use ($deferred, &$buffer, $abstractMethod, $counter, $httpMethod, $logUrl) {
                $this->logger->debug("HTTP -> #{$counter} Response: {$buffer}");

                $answer = json_decode($buffer, true);
                $buffer = null;

                if ($answer['ok'] === false) {
                    $exception = new TelegramBotApiException($answer['description'], $answer['error_code']);

                    if (isset($answer['parameters'])) {
                        $parameters = new ResponseParameters($answer['parameters']);
                        $exception->setParameters($parameters);
                    }

                    $this->logger->error("HTTP -> #{$counter} {$httpMethod} {$logUrl} Response error {$exception->getCode()}: {$exception->getMessage()}");

                    $deferred->reject($exception);
                    return;
                }

                $answer = $abstractMethod->buildResult($answer['result'] ?? []);

                $this->logger->info("HTTP -> #{$counter} {$httpMethod} {$logUrl} Success");

                $deferred->resolve($answer);
            });
        });

        $request->end($json);

        return $deferred->promise();
    }

}