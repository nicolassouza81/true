<?php

declare(strict_types=1);

namespace Core;

/**
 * Roteador simples com suporte a:
 *  - Métodos GET/POST/PUT/PATCH/DELETE
 *  - Parâmetros dinâmicos: /users/{id}
 *  - Middlewares por rota (array de FQCN ou closures)
 *  - Handler como [Controller::class, 'method'] ou closure
 */
final class Router
{
    /** @var array<string, array<int, array{pattern:string, regex:string, params:array, handler:mixed, middlewares:array}>> */
    private array $routes = [
        'GET'    => [],
        'POST'   => [],
        'PUT'    => [],
        'PATCH'  => [],
        'DELETE' => [],
    ];

    public function get(string $pattern, mixed $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $pattern, $handler, $middlewares);
    }

    public function post(string $pattern, mixed $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $pattern, $handler, $middlewares);
    }

    public function put(string $pattern, mixed $handler, array $middlewares = []): void
    {
        $this->addRoute('PUT', $pattern, $handler, $middlewares);
    }

    public function patch(string $pattern, mixed $handler, array $middlewares = []): void
    {
        $this->addRoute('PATCH', $pattern, $handler, $middlewares);
    }

    public function delete(string $pattern, mixed $handler, array $middlewares = []): void
    {
        $this->addRoute('DELETE', $pattern, $handler, $middlewares);
    }

    private function addRoute(string $method, string $pattern, mixed $handler, array $middlewares): void
    {
        $pattern = '/' . trim($pattern, '/');
        $paramNames = [];

        $regex = preg_replace_callback('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', function ($matches) use (&$paramNames) {
            $paramNames[] = $matches[1];
            return '([^/]+)';
        }, $pattern);

        $regex = '#^' . $regex . '$#';

        $this->routes[$method][] = [
            'pattern'     => $pattern,
            'regex'       => $regex,
            'params'      => $paramNames,
            'handler'     => $handler,
            'middlewares' => $middlewares,
        ];
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $uri = $request->uri();

        // Permite POST simular PUT/PATCH/DELETE via campo _method
        if ($method === 'POST' && isset($_POST['_method'])) {
            $override = strtoupper((string) $_POST['_method']);
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                $method = $override;
            }
        }

        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $route) {
            if (!preg_match($route['regex'], $uri, $matches)) {
                continue;
            }

            array_shift($matches);
            $params = array_combine($route['params'], $matches) ?: [];
            $request->setParams($params);

            $this->runMiddlewares($route['middlewares'], $request);
            $this->callHandler($route['handler'], $request);
            return;
        }

        Response::abort(404);
    }

    private function runMiddlewares(array $middlewares, Request $request): void
    {
        foreach ($middlewares as $middleware) {
            if (is_string($middleware) && class_exists($middleware)) {
                $instance = new $middleware();
                $instance->handle($request);
                continue;
            }

            if (is_callable($middleware)) {
                $middleware($request);
            }
        }
    }

    private function callHandler(mixed $handler, Request $request): void
    {
        if (is_callable($handler) && !is_array($handler)) {
            $handler($request);
            return;
        }

        if (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;

            if (!class_exists($class)) {
                Response::abort(500, "Controller não encontrado: {$class}");
            }

            $controller = new $class();

            if (!method_exists($controller, $method)) {
                Response::abort(500, "Método não encontrado: {$class}::{$method}");
            }

            $controller->{$method}($request);
            return;
        }

        Response::abort(500, 'Handler de rota inválido.');
    }
}
