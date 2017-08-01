<?php


namespace phpfuncbot\Telegram;


use React\Promise\PromiseInterface;
use Steelbot\TelegramBotApi\Method\AnswerInlineQuery;
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
    public function setWebhook(string $url, int $max_connections = 40, array $allowed_updates = null) : PromiseInterface
    {
        $vo = new SetWebhook($url);
        $vo->setMaxConnections($max_connections);
        if (!is_null($allowed_updates)) {
            $vo->setAllowedUpdates($allowed_updates);
        }
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

    public function answerInlineQuery(AnswerInlineQuery $answerInlineQuery) : PromiseInterface
    {
        return $this->transport->send($answerInlineQuery);
    }
}