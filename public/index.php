<?php

if (PHP_SAPI === 'cli-server') {
    $staticPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $staticFile = __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, $staticPath);

    if ($staticPath !== '/' && is_file($staticFile)) {
        return false;
    }
}

require_once __DIR__ . '/../config/Config.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = normalize_request_path($path);

route($method, $path);

function normalize_request_path(string $path): string
{
    $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?: '';
    $path = '/' . trim($path, '/');

    if ($basePath !== '') {
        $basePath = '/' . trim($basePath, '/');
        if ($path === $basePath) {
            return '/';
        }
        if (str_starts_with($path, $basePath . '/')) {
            $path = substr($path, strlen($basePath));
        }
    }

    return $path === '' ? '/' : $path;
}

function route(string $method, string $path): void
{
    $routes = [
        ['GET', '#^/$#', 'view', 'index.php'],
        ['GET|POST', '#^/login/?$#', 'view', 'login.php'],
        ['GET|POST', '#^/register/?$#', 'view', 'register.php'],
        ['GET', '#^/logout/?$#', 'view', 'logout.php'],
        ['GET', '#^/dashboard/?$#', 'view', 'dashboard.php'],

        ['POST', '#^/repair-orders/?$#', 'controller', 'ro_create.php'],
        ['GET', '#^/repair-orders/(\d+)/?$#', 'controller', 'ro_view.php', ['id' => 1]],
        ['POST', '#^/repair-orders/(\d+)/actions/?$#', 'controller', 'ro_action.php', ['ro_id' => 1]],
        ['POST', '#^/repair-orders/(\d+)/findings/?$#', 'controller', 'finding_action.php', ['ro_id' => 1]],
        ['GET|POST', '#^/repair-orders/(\d+)/inspection/?$#', 'controller', 'ro_inspect.php', ['id' => 1, 'ro_id' => 1]],

        ['GET', '#^/customer/review/([^/]+)/?$#', 'customer', 'customer_view.php', ['token' => 1]],
        ['POST', '#^/customer/review/([^/]+)/decision/?$#', 'customer', 'customer_decision.php', ['token' => 1]],

        // Legacy cleanups for bookmarks from the old direct-file URLs.
        ['GET|POST', '#^/login\.php$#', 'view', 'login.php'],
        ['GET|POST', '#^/register\.php$#', 'view', 'register.php'],
        ['GET', '#^/logout\.php$#', 'view', 'logout.php'],
        ['GET', '#^/dashboard\.php$#', 'view', 'dashboard.php'],
    ];

    foreach ($routes as $route) {
        [$allowedMethods, $pattern, $type, $file] = $route;
        if (!preg_match($pattern, $path, $matches)) {
            continue;
        }

        $methods = explode('|', $allowedMethods);
        if (!in_array($method, $methods, true)) {
            http_response_code(405);
            exit('Method not allowed.');
        }

        apply_route_params($route[4] ?? [], $matches);
        require route_file($type, $file);
        exit;
    }

    http_response_code(404);
    exit('Page not found.');
}

function apply_route_params(array $params, array $matches): void
{
    foreach ($params as $name => $index) {
        $value = rawurldecode($matches[$index] ?? '');
        if ($value === '') {
            continue;
        }

        $_GET[$name] = $_GET[$name] ?? $value;
        $_REQUEST[$name] = $_REQUEST[$name] ?? $value;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST[$name] = $_POST[$name] ?? $value;
        }
    }
}

function route_file(string $type, string $file): string
{
    return match ($type) {
        'view' => __DIR__ . '/../src/views/' . $file,
        'controller' => __DIR__ . '/../src/controller/' . $file,
        'customer' => __DIR__ . '/../src/views/customer/' . $file,
        default => throw new RuntimeException('Unknown route target.'),
    };
}
