<?php
// config/Config.php

$localConfigPath = __DIR__ . '/LocalConfig.php';
$localConfig = is_file($localConfigPath) ? require $localConfigPath : [];
if (!is_array($localConfig)) {
    throw new RuntimeException('config/LocalConfig.php must return an array.');
}

function config_value(string $key, mixed $default = null): mixed
{
    global $localConfig;

    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($value !== false && $value !== null && $value !== '') {
        return $value;
    }

    return $localConfig[$key] ?? $default;
}

function required_config_value(string $key): string
{
    $value = config_value($key);
    if ($value === null || $value === '') {
        throw new RuntimeException("Missing required configuration value: {$key}");
    }

    return (string) $value;
}

function default_base_url(): string
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $scriptDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

    if ($scriptDir === '' || $scriptDir === '.') {
        return '';
    }

    if (str_ends_with($scriptDir, '/public')) {
        $scriptDir = substr($scriptDir, 0, -7);
    }

    return $scriptDir === '' ? '' : $scriptDir;
}

define('APP_NAME', (string) config_value('ROFLOW_APP_NAME', 'ROFlow'));
define('APP_ENV', (string) config_value('ROFLOW_APP_ENV', 'local'));

// Public URL prefix. Leave empty when public/ is the document root.
define('BASE_URL', rtrim((string) config_value('ROFLOW_BASE_URL', default_base_url()), '/'));

define('DB_HOST', required_config_value('ROFLOW_DB_HOST'));
define('DB_PORT', (string) config_value('ROFLOW_DB_PORT', '5432'));
define('DB_USER', required_config_value('ROFLOW_DB_USER'));
define('DB_PASS', (string) config_value('ROFLOW_DB_PASS', ''));
define('DB_NAME', required_config_value('ROFLOW_DB_NAME'));

define('ASSET_URL', rtrim(BASE_URL . '/assets', '/'));

// Start the session exactly once. The secure flag follows the request scheme
// so local HTTP works while HTTPS deployments get secure cookies.
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? null) === '443');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
