<?php
include __DIR__ . '/vendor/autoload.php';

use phpfuncbot\Config\Config;
use phpfuncbot\Config\IniLoader;
use phpfuncbot\Telegram\API;
use phpfuncbot\Telegram\ReactHttpTransport;

$config = new Config(new IniLoader(__DIR__ . '/phpfuncbot.ini'));
$logger = new \phpfuncbot\Logger\FileLogger(__DIR__, $config->getLoggerFilePath());

$loop = React\EventLoop\Factory::create();
$api = new API(new ReactHttpTransport($config->getTelegramKey(), $loop, $logger));

$argShort = "h";
$argLong = ["webhook-install", "run-server", "webhook-info", "webhook-delete"];
$opts = getopt($argShort, $argLong);
if (isset($opts['h'])) {
    $opts = [];
}

if (isset($opts['webhook-install'])) {
    $promise = $api->setWebhook($config->getWebhookUrl(), $config->getWebhookConnectionLimit());
    try {
        $response = \phpfuncbot\Helpers\Promise::await($loop, $promise);
    } catch (\Exception $e) {
        echo "Webhook install error: {$e->getMessage()}\n";
        exit(-1);
    }
    echo "Webhook was set\n";
}

if (isset($opts['webhook-info'])) {
    $promise = $api->getWebhookInfo();
    try {
        $response = \phpfuncbot\Helpers\Promise::await($loop, $promise);
    } catch (\Exception $e) {
        echo "Webhook delete error: {$e->getMessage()}\n";
        exit(-1);
    }
    echo print_r($response, true)."\n";
}

if (isset($opts['webhook-delete'])) {
    $promise = $api->deleteWebhook();
    try {
        $response = \phpfuncbot\Helpers\Promise::await($loop, $promise);
    } catch (\Exception $e) {
        echo "Webhook delete error: {$e->getMessage()}\n";
        exit(-1);
    }
    echo "Webhook was deleted\n";
}

if (empty($opts)) {
    echo "Usage: {$_SERVER['argv'][0]} "
        . ($argShort ? "[-{$argShort}] " : "")
        . implode(" ", array_map(function ($item) {
            return "[--{$item}]";
        }, $argLong)) . "\n";
}