<?php

require_once __DIR__ . '/../../config/Config.php';

/**
 * Escape output for safe HTML rendering.
 * Use EVERYWHERE you echo a variable into HTML.
 */
function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string {
    $path = clean_route_path($path);
    return rtrim(BASE_URL, '/') . $path;
}

function asset_url(string $path): string {
    return rtrim(ASSET_URL, '/') . '/' . ltrim($path, '/');
}

function repair_order_url(int $roId): string {
    return url('/repair-orders/' . $roId);
}

function repair_order_action_url(int $roId): string {
    return url('/repair-orders/' . $roId . '/actions');
}

function repair_order_findings_url(int $roId): string {
    return url('/repair-orders/' . $roId . '/findings');
}

function inspection_url(int $roId): string {
    return url('/repair-orders/' . $roId . '/inspection');
}

function customer_review_url(string $token): string {
    return url('/customer/review/' . rawurlencode($token));
}

function customer_decision_url(string $token): string {
    return url('/customer/review/' . rawurlencode($token) . '/decision');
}

function clean_route_path(string $path): string {
    $path = trim($path);
    if ($path === '' || $path === '/') {
        return '/';
    }

    $parts = parse_url($path);
    $route = $parts['path'] ?? $path;
    $query = isset($parts['query']) ? '?' . $parts['query'] : '';

    $legacyRoutes = [
        'index.php' => '/',
        '/index.php' => '/',
        'login.php' => '/login',
        '/login.php' => '/login',
        'register.php' => '/register',
        '/register.php' => '/register',
        'logout.php' => '/logout',
        '/logout.php' => '/logout',
        'dashboard.php' => '/dashboard',
        '/dashboard.php' => '/dashboard',
    ];

    if (isset($legacyRoutes[$route])) {
        return $legacyRoutes[$route];
    }

    if (!str_starts_with($route, '/')) {
        $route = '/' . $route;
    }

    return rtrim($route, '/') . $query;
}

/**
 * Redirect to a clean app URL, then exit.
 */
function redirect(string $path): void {
    header('Location: ' . url($path));
    exit;
}

/**
 * Set a flash message (shown once on the next page render).
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear all flash messages.
 */
function get_flash(): array {
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flash;
}

/**
 * Generate or retrieve the CSRF token for this session.
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify a submitted CSRF token. Call at the top of every POST handler.
 */
function verify_csrf(): void {
    $submitted = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $submitted)) {
        http_response_code(419);
        die('Invalid CSRF token.');
    }
}

/**
 * Render a hidden CSRF input — drop into every <form>.
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Format a date for display.
 */
function format_date(?string $datetime, string $format = 'M j, Y g:ia'): string {
    if (!$datetime) return '—';
    return date($format, strtotime($datetime));
}

/**
 * Generate a unique RO number like "RO-2026-0042".
 */
function generate_ro_number(int $sequence): string {
    return sprintf('RO-%d-%04d', (int)date('Y'), $sequence);
}

/**
 * Generate a random tokenized URL slug for customer approval links.
 */
function generate_customer_token(): string {
    return 'tok_' . bin2hex(random_bytes(16));
}
