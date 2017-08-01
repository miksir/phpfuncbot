<?php


namespace phpfuncbot\Telegram;


use Steelbot\TelegramBotApi\Type\Update;

interface ServerObserver
{
    public function handleUpdateRequest(Update $update);
}