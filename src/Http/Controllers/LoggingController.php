<?php

namespace LaraBug\Http\Controllers;

use LaraBug\Jobs\ProcessException;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class LoggingController
{
    public static function log($data)
    {
        $exception = $data['exception'];
        dispatch_sync(new ProcessException([
            'id' => $id = Uuid::uuid4(),
            'host' => $exception['host'],
            'error' => $exception['error'],
            'additional' => [],
            'method' => $exception['method'],
            'class' => $exception['class'],
            'file' => $exception['file'],
            'file_type' => $exception['file_type']?? 'php',
            'line' => $exception['line'],
            'fullUrl' => $exception['fullUrl'],
            'executor' => $exception['executor'],
            'storage' => $exception['storage'],
            'exception' => substr($exception['exception'],0, 10000),
            'user' => $data['user']
        ], now()));

        return  $id->toString();
    }

}
