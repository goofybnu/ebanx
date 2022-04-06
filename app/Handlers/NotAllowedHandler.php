<?php

namespace App\Handlers;

class NotAllowedHandler
{

    public function __invoke($request, $response)
    {
        return $response
            ->withStatus(403)
            ->withHeader('Content-Type', 'application/json')
            ->withJson(['error' => true, 'message' => 'Not allowed!']);
    }
}
