<?php

use phpfuncbot\Config\Config;
use phpfuncbot\Config\IniLoader;

include __DIR__ . '/../vendor/autoload.php';

$config = new Config(new IniLoader(__DIR__ . '/../phpfuncbot.ini'));

$loop = React\EventLoop\Factory::create();

$opts = getopt('', ['dir:']);

if (empty($opts['dir'])) {
    echo "Usage: {$_SERVER['argv'][0]} --dir ./phpdoc/\n";
    exit(-1);
}
$dir = $opts['dir'];
$dirh = opendir($dir);

$client = new Predis\Async\Client($config->getRedisServer(), $loop);
$client->connect(function ($client) use ($loop, $dirh) {
    $file = readdir($dirh);

});