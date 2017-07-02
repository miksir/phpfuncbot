<?php


namespace phpfuncbot\Telegram\Response;


class Response
{
    /**
     * @var array
     */
    private $response;

    /**
     * Response constructor.
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return (bool)(!$this->response['ok'] ?? true);
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return (string)$this->response['description'] ?? 'Malformed response';
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
       return $this->response['result'] ?? null;
    }
}