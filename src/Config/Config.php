<?php


namespace phpfuncbot\Config;


class Config
{
    /**
     * @var Loader
     */
    private $loader;

    public function __construct(Loader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * @return string
     * @throws ConfigException
     */
    public function getTelegramKey() : string
    {
        return $this->get('telegram', 'key');
    }

    public function getWebhookUrl() : string
    {
        return $this->get('telegram-webhook', 'url');
    }

    public function getWebhookConnectionLimit() : int
    {
        return $this->get('telegram-webhook', 'max_connections');
    }

    public function getLoggerFilePath() : string
    {
        return $this->get('logger', 'filename');
    }

    public function getServerListen() : string
    {
        return $this->get('server', 'listen');
    }

    public function getServerHTTPPath() : string
    {
        return $this->get('server', 'path');
    }


    /**
     * @param string $section
     * @param string $key
     * @return mixed
     * @throws ConfigException
     */
    private function get(string $section, string $key)
    {
        $data = $this->loader->getArray($section);
        if (!isset($data[$key])) {
            throw new ConfigException("Config key {$section}.{$key} not found");
        }
        return $data[$key];
    }
}