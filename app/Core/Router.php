<?php

namespace App\Core;

use Exception;

class Router
{
    private static array $routes = [];
    private static array $namedRoutes = [];

    public static function get(string $path, array|callable $handler, array $middleware = []): void
    {
        self::addRoute('GET', $path, $handler, $middleware);
    }

    public static function post(string $path, array|callable $handler, array $middleware = []): void
    {
        self::addRoute('POST', $path, $handler, $middleware);
    }

    public static function put(string $path, array|callable $handler, array $middleware = []): void
    {
        self::addRoute('PUT', $path, $handler, $middleware);
    }

    public static function delete(string $path, array|callable $handler, array $middleware = []): void
    {
        self::addRoute('DELETE', $path, $handler, $middleware);
    }

    private static function addRoute(string $method, string $path, array|callable $handler, array $middleware = []): void
    {
        $normalizedPath = '/' . trim($path, '/');
        if ($normalizedPath === '//') {
            $normalizedPath = '/';
        }

        self::$routes[] = [
            'method'     => $method,
            'path'       => $normalizedPath,
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    public static function dispatch(Request $request, Response $response): void
    {
        $requestMethod = $request->getMethod();
        $requestUri = $request->getUri();

        foreach (self::$routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            $pattern = self::convertToRegex($route['path']);
            if (preg_match($pattern, $requestUri, $matches)) {
                // Extract named params
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = urldecode($value);
                    }
                }

                // Execute middleware pipeline
                foreach ($route['middleware'] as $mw) {
                    if (is_string($mw)) {
                        $mwClass = class_exists($mw) ? $mw : "App\\Middleware\\{$mw}";
                        if (class_exists($mwClass)) {
                            $mwInstance = new $mwClass();
                            $mwInstance->handle($request, $response);
                        }
                    } elseif (is_callable($mw)) {
                        $mw($request, $response);
                    }
                }

                // Execute handler
                $handler = $route['handler'];
                $argList = array_values($params);

                if (is_callable($handler)) {
                    call_user_func_array($handler, array_merge([$request, $response], $argList));
                    return;
                }

                if (is_array($handler)) {
                    [$controllerClass, $method] = $handler;
                    if (!class_exists($controllerClass)) {
                        throw new Exception("Controller class {$controllerClass} not found.");
                    }
                    $controller = new $controllerClass();
                    if (!method_exists($controller, $method)) {
                        throw new Exception("Method {$method} not found in {$controllerClass}.");
                    }
                    $controller->setRequest($request);
                    $controller->setResponse($response);
                    call_user_func_array([$controller, $method], $argList);
                    return;
                }
            }
        }

        // 404 Not Found
        $response->setStatusCode(404);
        http_response_code(404);
        if ($request->isAjax()) {
            $response->json(['success' => false, 'message' => 'Sayfa veya kaynak bulunamadı (404).'], 404);
        } else {
            $response->sendHeaders();
            $viewsPath = dirname(__DIR__, 2) . '/views/';
            $errorView = $viewsPath . 'errors/404.php';
            $layoutFile = $viewsPath . 'layouts/main.php';

            $pageTitle = '404 - Sayfa Bulunamadı - SocialNet';
            ob_start();
            if (file_exists($errorView)) {
                require $errorView;
            } else {
                echo "<div style='text-align:center;padding:50px;'><h1>404</h1><p>Aradığınız sayfa bulunamadı.</p><a href='" . url('/') . "'>Ana Sayfaya Dön</a></div>";
            }
            $content = ob_get_clean();

            if (file_exists($layoutFile)) {
                require $layoutFile;
            } else {
                echo $content;
            }
        }
    }

    private static function convertToRegex(string $path): string
    {
        // Replace {param} with (?P<param>[^/]+)
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    public static function getRoutes(): array
    {
        return self::$routes;
    }
}
