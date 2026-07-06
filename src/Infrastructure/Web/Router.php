<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web;

class Router
{
    private array $routes = [];
    private array $middleware = [];

    public function addRoute(string $method, string $pattern, string $controller, string $action): void
    {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'controller' => $controller,
            'action' => $action,
        ];
    }

    public function get(string $pattern, string $controller, string $action): void
    {
        $this->addRoute('GET', $pattern, $controller, $action);
    }

    public function post(string $pattern, string $controller, string $action): void
    {
        $this->addRoute('POST', $pattern, $controller, $action);
    }

    public function put(string $pattern, string $controller, string $action): void
    {
        $this->addRoute('PUT', $pattern, $controller, $action);
    }

    public function delete(string $pattern, string $controller, string $action): void
    {
        $this->addRoute('DELETE', $pattern, $controller, $action);
    }

    public function addMiddleware(callable $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    public function dispatch(string $method, string $uri): ?array
    {
        $method = strtoupper($method);
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                return [
                    'controller' => $route['controller'],
                    'action' => $route['action'],
                    'params' => $params,
                ];
            }
        }

        return null;
    }

    public function runMiddleware(): void
    {
        foreach ($this->middleware as $mw) {
            $mw();
        }
    }

    public function loadRoutes(array $routeDefinitions): void
    {
        foreach ($routeDefinitions as $definition) {
            $this->addRoute(
                $definition['method'] ?? 'GET',
                $definition['pattern'],
                $definition['controller'],
                $definition['action']
            );
        }
    }
}
