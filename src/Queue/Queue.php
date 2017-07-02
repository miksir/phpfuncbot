<?php


namespace phpfuncbot\Queue;


interface Queue
{
    public function push($id, $message);
}