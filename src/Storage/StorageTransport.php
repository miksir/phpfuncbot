<?php


namespace phpfuncbot\Storage;


interface StorageTransport
{
    public function set(string $key, string $value);
    public function get(string $key): string;
}