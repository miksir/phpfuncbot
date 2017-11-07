<?php


namespace phpfuncbot\Index;


use phpfuncbot\Storage\StorageTransport;

class TrigramRedisIndex implements Index
{
    /**
     * @var StorageTransport
     */
    private $storageTransport;

    public function __construct(StorageTransport $storageTransport)
    {
        $this->storageTransport = $storageTransport;
    }

    /**
     * @param string $text
     * @return array|string[]
     */
    private function createTrigrams(string $text): array
    {
        $trigrams = [];
        foreach (array_filter(preg_split('/(\s+|::|_|(?=[A-Z]))/', $text)) as $str) {
            $trigram = [];
            $str = strtolower($str);
            //$count = strlen($text)+2;
            $str = '__' . $str . '__';
            $count = strlen($str) - 2;
            for ($i = 0; $i < $count; $i++) {
                //$trigrams[] = substr($text, max($i-2, 0), min($i+1,3));
                $index = substr($str, $i, 3);
                $trigram[$index . ':' . $i] = "1";
                if ($i !== 0) {
                    $trigram[$index . ':' . ($i-1)] = "2";
                }
                $trigram[$index . ':' . ($i+1)] = "2";
            }
            $trigrams[] = $trigram;
        }
        return $trigrams;
    }

    public function createIndex(string $wordsForSearch, int $id, string $weight = "A")
    {
        if (strpos($wordsForSearch, '::') !== false) {
            $words = explode('::', $wordsForSearch);
            $this->createIndex($words[0], $id, $weight * 0.9);
            $this->createIndex($words[1], $id);
            return;
        }
        $trigrams = $this->createTrigrams($wordsForSearch);

    }

    public function searchIndex(string $wordsForSearch)
    {
        // TODO: Implement searchIndex() method.
    }

    public function putDocument(int $id, string $text)
    {

    }
}