<?php

namespace App\Core;

class Router
{
    private static array $routes = [];
    private static bool $routesLoaded = false;

    public static function get(string $uri, string $controller, string $action, array $middlewares = []): void
    {
        self::addRoute('GET', $uri, $controller, $action, $middlewares);
    }

    public static function post(string $uri, string $controller, string $action, array $middlewares = []): void
    {
        self::addRoute('POST', $uri, $controller, $action, $middlewares);
    }

    private static function addRoute(string $method, string $uri, string $controller, string $action, array $middlewares): void
    {
        self::$routes[$method][$uri] = compact('controller', 'action', 'middlewares');
    }

    public static function resolve(): void
    {
        if (!self::$routesLoaded) {
            self::loadRoutes();
        }

        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        $route = self::$routes[$method][$uri] ?? null;

        if ($route) {
            self::handleMiddlewares($route['middlewares'] ?? []);
            $controller = new $route['controller']();
            $controller->{$route['action']}();
        } else {
            http_response_code(404);
            require dirname(__DIR__, 2) . '/templates/404.php';
        }
    }

    private static function handleMiddlewares(array $middlewares): void
    {
        foreach ($middlewares as $middleware) {
            $methodName = 'run' . ucfirst($middleware) . 'Middleware';
            if (method_exists(self::class, $methodName)) {
                self::$methodName();
            } else {
                throw new \Exception("Middleware '$middleware' non supporté.");
            }
        }
    }

    private static function loadRoutes(): void
    {
        require dirname(__DIR__, 2) . '/routes/route.web.php';
        self::$routesLoaded = true;
    }

    // Exemple de middlewares que tu peux implémenter dans des méthodes séparées
    private static function runAuthMiddleware(): void
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            header('Location: /');
            exit;
        }
    }

    private static function runGuestMiddleware(): void
    {
        session_start();
        if (isset($_SESSION['user'])) {
            header('Location: /dashboard');
            exit;
        }
    }
}
