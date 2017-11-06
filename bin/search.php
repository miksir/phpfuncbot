<?php

use phpfuncbot\Config\Config;
use phpfuncbot\Config\IniLoader;
use phpfuncbot\Index\TrigramIndex;
use Predis\Client;

include __DIR__ . '/../vendor/autoload.php';

$config = new Config(new IniLoader(__DIR__ . '/../phpfuncbot.ini'));
$trigramHelper = TrigramIndex::create();

$client = new Client($config->getRedisServer());

if (empty($_SERVER['argv'][1])) {
    echo "Usage: {$_SERVER['argv'][0]} <search text>\n";
    exit(-1);
}
$text = $_SERVER['argv'][1];

$trigrams = $trigramHelper->parseForIndex($text);
exit();

$ids = [];
foreach ($trigrams as $trigram) {
    $docIds = $client->lrange("tri:{$trigram}", 0, -1);
    foreach ($docIds as $docId) {
        $ids[$docId] = ($ids[$docId] ?? 0)+1;
    }
}

arsort($ids, SORT_NUMERIC);
$ids = array_slice($ids, 0, 5, true);
print_r($ids);

foreach ($ids as $id=>$cnt) {
    $string = $client->get("func:{$id}");
    echo "{$string}\n";
}
