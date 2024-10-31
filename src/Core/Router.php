<?php

namespace App\Core;

use RuntimeException;

class Router
{
    private array $routes = [];
    private array $middlewares = [];

    public function get(string $path, array $handler): self
    {
        $this->routes['GET'][$path] = $handler;
        return $this;
    }

    public function post(string $path, array $handler): self
    {
        $this->routes['POST'][$path] = $handler;
        return $this;
    }

    public function delete(string $path, array $handler): self
    {
        $this->routes['DELETE'][$path] = $handler;
        return $this;
    }

    public function addMiddleware(callable $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    public function dispatch(string $method, string $uri): mixed
    {
        // Run middlewares
        foreach ($this->middlewares as $middleware) {
            $middleware();
        }

        // Handle OPTIONS request for CORS
        if ($method === 'OPTIONS') {
            return null;
        }

        // Check if method exists
        if (!isset($this->routes[$method])) {
            throw new RuntimeException('Method not allowed', 405);
        }

        // Find matching route
        foreach ($this->routes[$method] as $path => $handler) {
            if ($this->matchRoute($path, $uri)) {
                return call_user_func($handler);
            }
        }

        throw new RuntimeException('Not Found', 404);
    }

    private function matchRoute(string $path, string $uri): bool
    {
        $path = trim($path, '/');
        $uri = trim($uri, '/');
        return $path === $uri;
    }
}
