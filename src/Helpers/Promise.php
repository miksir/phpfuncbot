<?php


namespace phpfuncbot\Helpers;


use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;

class Promise
{
    public static function await(LoopInterface $loop, PromiseInterface $promise)
    {
        $wait = true;
        $resolved = null;
        /** @var \Exception $exception */
        $exception = null;

        $promise->then(
            function ($c) use (&$resolved, &$wait, $loop) {
                $resolved = $c;
                $wait = false;
                $loop->stop();
            },
            function (\Exception $error) use (&$exception, &$wait, $loop) {
                $exception = $error;
                $wait = false;
                $loop->stop();
            }
        );

        while ($wait) {
            $loop->run();
        }

        if ($exception !== null) {
            throw $exception;
        }

        return $resolved;
    }
}