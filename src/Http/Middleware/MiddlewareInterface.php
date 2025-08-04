<?php

namespace App\Http\Middleware;

use App\Http\Request;
use App\Http\Response;

/**
 * Interface for HTTP Middleware.
 * Any class implementing this interface can be used as a middleware in the application.
 */
interface MiddlewareInterface
{
    /**
     * Handle the incoming request.
     *
     * @param Request $request The current HTTP request object.
     * @param Response $response The current HTTP response object.
     * @return void
     */
    public function handle(Request $request, Response $response): void;
}