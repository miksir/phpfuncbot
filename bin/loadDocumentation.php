<?php

use phpfuncbot\Config\Config;
use phpfuncbot\Config\IniLoader;
use phpfuncbot\Index\TrigramIndex;
use Predis\Client;
use Symfony\Component\DomCrawler\Crawler;

include __DIR__ . '/../vendor/autoload.php';

$config = new Config(new IniLoader(__DIR__ . '/../phpfuncbot.ini'));
$trigramHelper = TrigramIndex::create();

$opts = getopt('', ['dir:']);

if (empty($opts['dir'])) {
    echo "Usage: {$_SERVER['argv'][0]} --dir <path to phpdoc dir>\n";
    exit(-1);
}

$client = new Client($config->getRedisServer());
$client->flushdb();

$dir = $opts['dir'];
$id = 1;
$dirh = opendir($dir);
while ($file = readdir($dirh)) {
    if (!substr($file, -5) === '.html') {
        continue;
    }
    if (substr($file, 0, 6) === 'class.') {
        continue;
    }
    $document = new Crawler(file_get_contents($dir . '/' . $file));
    if (!$document) {
        continue;
    }

    $headers = $document->filter("H1.refname");
    if (!count($headers)) {
        continue;
    }

    $syn = $document->filter('.methodsynopsis')->eq(0);
    if (!count($syn)) {
        continue;
    }

    $fnames = $headers->each(function (Crawler $header) use ($client, $trigram, $id) {
        $header = trim(strip_tags($header->html()));;
        echo "{$header}\n";
        return $header;
    });

    if (!$fnames) {
        continue;
    }

    $fname = null;
    foreach ($fnames as $item) {
        if (strpos($item, '::') !== false) {
            list(,$fname) = explode('::', $item);
            break;
        }
    }
    if (!$fname) {
        $fname = $fnames[0];
    }

    $trigrams = $trigramHelper->parseForIndex($fname);
    foreach ($trigrams as $i=>$trigram) {
        $client->rpush("tri:{$trigram}", $id);
    }
    $client->set("tricount:{$id}", count($trigrams));

    $text = trim(str_replace("\n", "", strip_tags($syn->html())));
    $text = preg_replace('/\s*([()])\s*/', '\1', $text);
    $text = preg_replace('/\s*([\[\]])\s*/', '\1', $text);
    $text = preg_replace('/\s+,/', ',', $text);
    echo "  {$text}\n";
    $client->set("func:{$id}", $text);

    $id++;
}
closedir($dirh);
