<?php


namespace phpfuncbot\Config;


interface Loader
{
    public function getArray(string $section): array;
}