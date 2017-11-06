<?php


namespace phpfuncbot\Index;


interface Index
{
    public function createIndex(string $wordsForSearch, int $id, int $weight = 1);
    public function searchIndex(string $wordsForSearch);
}