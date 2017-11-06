<?php


namespace phpfuncbot\Index;


class TrigramIndex implements Index
{
    static public function create()
    {
        return new TrigramIndex();
    }

    /**
     * @param string $text
     * @return array|string[]
     */
    public function parseForIndex(string $text): array
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
                $trigram[$index . ':' . $i] = 1;
                if ($i !== 0) {
                    $trigram[$index . ':' . ($i-1)] = 0.5;
                }
                $trigram[$index . ':' . ($i+1)] = 0.5;
            }
            $trigrams[] = $trigram;
        }
        print_r($trigrams);
        return $trigrams;
    }

    public function createIndex(string $wordsForSearch, int $id, int $weight = 1)
    {
        if (strpos($wordsForSearch, '::') !== false) {
            $words = explode('::', $wordsForSearch);
            $this->createIndex($words[0], $id, $weight * 0.9);
            $this->createIndex($words[1], $id);
        }
        $wordsB = array_filter(preg_split('/(\s+|::|_|(?=[A-Z]))/', $wordsForSearch));

    }

    public function searchIndex(string $wordsForSearch)
    {
        // TODO: Implement searchIndex() method.
    }
}