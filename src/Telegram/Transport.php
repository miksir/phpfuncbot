<?php


namespace phpfuncbot\Telegram;


use React\Promise\PromiseInterface;
use Steelbot\TelegramBotApi\Method\AbstractMethod;

interface Transport
{
    public function send(AbstractMethod $abstractMethod) : PromiseInterface;
}