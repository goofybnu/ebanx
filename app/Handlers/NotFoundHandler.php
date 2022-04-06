<?php

namespace App\Handlers;

class NotFoundHandler
{

    public function __invoke($request, $response)
    {
        return $response
            ->withStatus(404)
            ->withHeader('Content-Type', 'application/json')
            ->withJson(['error' => true, 'message' => 'Not found!']);
    }
}
