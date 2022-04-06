<?php

namespace App\Handlers;

class ErrorHandler
{

    public function __invoke($request, $response, $exception)
    {
        // if ($_SERVER["REMOTE_ADDR"] == '127.0.0.1' or $_SERVER["REMOTE_ADDR"] ==  '::1') {
            $return = [
                'error' => true,
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ];
            /*
        } else {
            $return = [
                'error' => true,
                'code' => $exception->getCode(),
                'message' => 'Something went wrong!'
            ];
        }
        */
        return $response
            ->withStatus(500)
            ->withHeader('Content-Type', 'application/json')
            ->withJson($return);
    }
}
