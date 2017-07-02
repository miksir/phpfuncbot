<?php


namespace phpfuncbot\Telegram;


use React\Promise\PromiseInterface;
use Steelbot\TelegramBotApi\Method\DeleteWebhook;
use Steelbot\TelegramBotApi\Method\GetWebhookInfo;
use Steelbot\TelegramBotApi\Method\SetWebhook;

class API
{
    /**
     * @var Transport
     */
    private $transport;

    public function __construct(Transport $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @param string $url
     * @param int $max_connections
     * @param string[] $allowed_updates
     * @return PromiseInterface
     */
    public function setWebhook(string $url, int $max_connections = 40, array $allowed_updates = ['message']) : PromiseInterface
    {
        $vo = new SetWebhook($url);
        $vo->setMaxConnections($max_connections);
        $vo->setAllowedUpdates($allowed_updates);
        $promise = $this->transport->send($vo);
        return $promise;
    }

    /**
     * @return PromiseInterface
     */
    public function deleteWebhook() : PromiseInterface
    {
        $vo = new DeleteWebhook();
        $promise = $this->transport->send($vo);
        return $promise;
    }

    /**
     * @return PromiseInterface
     */
    public function getWebhookInfo() : PromiseInterface
    {
        $vo = new GetWebhookInfo();
        $promise = $this->transport->send($vo);
        return $promise;
    }
}