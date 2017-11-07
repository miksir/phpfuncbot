<?php


namespace phpfuncbot\Phpfunc;


use phpfuncbot\Index\TrigramRedisIndex;
use phpfuncbot\Telegram\API;
use phpfuncbot\Telegram\ServerObserver;
use Psr\Log\LoggerInterface;
use Steelbot\TelegramBotApi\InlineQueryResult\InlineQueryResultArticle;
use Steelbot\TelegramBotApi\InputMessageContent\InputTextMessageContent;
use Steelbot\TelegramBotApi\Method\AnswerInlineQuery;
use Steelbot\TelegramBotApi\Type\InlineQuery;
use Steelbot\TelegramBotApi\Type\Update;

class PhpfuncServerObserver implements ServerObserver
{
    /**
     * @var API
     */
    private $api;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var \phpfuncbot\Index\TrigramRedisIndex
     */
    private $trigram;

    /**
     * PhpfuncServerObserver constructor.
     * @param API $api
     * @param TrigramRedisIndex $trigram
     * @param LoggerInterface $logger
     */
    public function __construct(
        API $api,
        TrigramRedisIndex $trigram,
        LoggerInterface $logger
    )
    {
        $this->api = $api;
        $this->logger = $logger;
        $this->trigram = $trigram;
    }

    public function handleUpdateRequest(Update $update)
    {
        if ($update->inlineQuery instanceof InlineQuery) {
            $query = $update->inlineQuery->query;
            $results = [];
            if ($query) {
                //$trigrams = $this->trigram->parseForIndex($query);
            }

            $results = [
                new InlineQueryResultArticle(1, $update->inlineQuery->query, new InputTextMessageContent('Hello test 1')),
                new InlineQueryResultArticle(2, 'Test 2', new InputTextMessageContent('Hello test 2')),
                new InlineQueryResultArticle(3, 'Test 3', new InputTextMessageContent('Hello test 3')),
            ];
            $answer = new AnswerInlineQuery(
                $update->inlineQuery->id,
                $results
            );
            $this->api->answerInlineQuery($answer);
        }
    }
}