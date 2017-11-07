<?php


namespace phpfuncbot\Index;


interface Index
{
    public function createIndex(string $wordsForSearch, int $id, string $weight = "A");
    public function searchIndex(string $wordsForSearch);
    public function putDocument(int $id, string $text);
}