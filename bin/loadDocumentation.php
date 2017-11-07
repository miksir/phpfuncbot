<?php

use phpfuncbot\Config\Config;
use phpfuncbot\Config\IniLoader;
use phpfuncbot\Index\TrigramRedisIndex;
use Symfony\Component\DomCrawler\Crawler;

include __DIR__ . '/../vendor/autoload.php';

$config = new Config(new IniLoader(__DIR__ . '/../phpfuncbot.ini'));
$logger = new \phpfuncbot\Logger\ConsoleLogger();

$opts = getopt('', ['dir:']);

if (empty($opts['dir'])) {
    $logger->critical("Usage: {$_SERVER['argv'][0]} --dir <path to phpdoc dir>");
    exit(-1);
}

$transport = new \phpfuncbot\Storage\RedisTransport($config->getRedisServer(), $logger);
$trigramIndex = new TrigramRedisIndex($transport);
$transport->flushdb();

$dirs = new RecursiveDirectoryIterator($opts['dir']);
$iterator = new RecursiveIteratorIterator($dirs);
$regexIterator = new RegexIterator($iterator, '/^class\..+\.html$/i', RecursiveRegexIterator::GET_MATCH);

$id = 1;
foreach ($regexIterator as $file) {
    $logger->info("File: {$file}");

    $document = new Crawler(file_get_contents($file));
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

    $fnames = $headers->each(function (Crawler $header) use ($logger) {
        $header = trim(strip_tags($header->html()));
        $logger->info("Header: {$header}");
        return $header;
    });

    if (!$fnames) {
        continue;
    }

    $fname = null;
    foreach ($fnames as $item) {
        $logger->info("-- {$item}");
        if (strpos($item, '::') !== false) {
            $words = explode('::', $item);
            $trigramIndex->createIndex($words[1], $id);
            $trigramIndex->createIndex($words[0], $id, 'B');
        } else {
            $trigramIndex->createIndex($item, $id);
        }

    }

    $text = trim(str_replace("\n", "", strip_tags($syn->html())));
    $text = preg_replace('/\s*([()])\s*/', '\1', $text);
    $text = preg_replace('/\s*([\[\]])\s*/', '\1', $text);
    $text = preg_replace('/\s+,/', ',', $text);

    $logger->info("  {$text}");
    $trigramIndex->putDocument($id, $text);

    $id++;
}
