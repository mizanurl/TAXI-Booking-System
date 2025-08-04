<?php

namespace App\Core;

use App\Http\Request;
use App\Http\Response;
use App\Http\Middleware\MiddlewareInterface;
use App\Exceptions\NotFoundException; // Ensure this is imported
use App\Exceptions\MethodNotAllowedException; // Ensure this is imported

class Router
{
    protected array $routes = [];
    protected array $middlewares = [];
    protected Request $request;
    protected Response $response;

    public function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();
    }

    /**
     * Add a GET route.
     * @param string $path
     * @param callable|string $handler
     */
    public function get(string $path, callable|string $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    /**
     * Add a POST route.
     * @param string $path
     * @param callable|string $handler
     */
    public function post(string $path, callable|string $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    /**
     * Add a PUT route.
     * @param string $path
     * @param callable|string $handler
     */
    public function put(string $path, callable|string $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    /**
     * Add a DELETE route.
     * @param string $path
     * @param callable|string $handler
     */
    public function delete(string $path, callable|string $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * Add a PATCH route.
     * @param string $path
     * @param callable|string $handler
     */
    public function patch(string $path, callable|string $handler): void
    {
        $this->addRoute('PATCH', $path, $handler);
    }

    /**
     * Add a route to the collection.
     * @param string $method
     * @param string $path
     * @param callable|string $handler
     */
    protected function addRoute(string $method, string $path, callable|string $handler): void
    {
        // Convert path to a regex pattern to handle route parameters like {id}
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#'; // Add delimiters for regex

        $this->routes[$method][$pattern] = $handler;
    }

    /**
     * Add a global middleware.
     * @param MiddlewareInterface $middleware
     */
    public function addMiddleware(MiddlewareInterface $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * Resolve the current request.
     * @throws NotFoundException If no route is found for the URI.
     * @throws MethodNotAllowedException If the method is not allowed for the URI.
     * @return array Returns the handler and parameters.
     */
    public function resolve(): array
    {
        $method = $this->request->method;
        $uri = $this->request->uri;

        // Run global middlewares
        foreach ($this->middlewares as $middleware) {
            $middleware->handle($this->request, $this->response);
        }

        if (!isset($this->routes[$method])) {
            // Check if URI exists for other methods before throwing MethodNotAllowed
            foreach ($this->routes as $allowedMethod => $patterns) {
                foreach ($patterns as $pattern => $handler) {
                    if (preg_match($pattern, $uri)) {
                        throw new MethodNotAllowedException("Method {$method} not allowed for {$uri}. Allowed methods: " . implode(', ', array_keys($this->routes)));
                    }
                }
            }
            // If URI doesn't match any route at all
            throw new NotFoundException("No route found for {$uri}");
        }

        foreach ($this->routes[$method] as $pattern => $handler) {
            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                return [$handler, $params];
            }
        }

        throw new NotFoundException("No route found for {$uri}");
    }
}