<?php

namespace App\Http\Middleware;

use App\Http\Request;
use App\Http\Response;
use App\Config\AppConfig;

class CorsMiddleware implements MiddlewareInterface
{
    private AppConfig $appConfig;

    public function __construct()
    {
        // Instantiate AppConfig to get CORS settings from your .env file
        $this->appConfig = new AppConfig();
    }

    /**
     * Handle the incoming request to apply CORS headers.
     *
     * @param Request $request The current HTTP request.
     * @param Response $response The current HTTP response.
     * @return void
     */
    public function handle(Request $request, Response $response): void
    {
        $allowedOrigins = $this->appConfig->corsAllowedOrigins;

        // Set Access-Control-Allow-Origin header
        if ($allowedOrigins === '*') {
            header('Access-Control-Allow-Origin: *');
        } else {
            // For specific origins, check if the request origin is allowed
            $origin = $request->headers['Origin'] ?? '';
            $allowedOriginsArray = array_map('trim', explode(',', $allowedOrigins));

            if (in_array($origin, $allowedOriginsArray)) {
                header("Access-Control-Allow-Origin: {$origin}");
            } else {
                // If origin is not allowed, you might want to log this or handle it differently.
                // For now, we'll just not set the header, which will cause a CORS error for disallowed origins.
            }
        }

        // Set allowed HTTP methods
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');

        // Set allowed headers (e.g., Content-Type, Authorization for API tokens)
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

        // Set max age for preflight requests (how long results of a preflight can be cached)
        header('Access-Control-Max-Age: 86400'); // Cache preflight requests for 24 hours (24 * 60 * 60)

        // Handle preflight (OPTIONS) requests
        // Browsers send an OPTIONS request before actual cross-origin requests
        // to check what methods and headers are allowed.
        if ($request->method === 'OPTIONS') {
            http_response_code(204); // Respond with No Content for preflight success
            exit(); // Terminate script execution after sending preflight response
        }
    }
}