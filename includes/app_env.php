<?php
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

if (!function_exists('app_load_env')) {
    function app_load_env(): void
    {
        static $loaded = false;

        if ($loaded) {
            return;
        }

        $autoloadPath = APP_ROOT . '/vendor/autoload.php';
        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
        }

        if (class_exists(Dotenv\Dotenv::class)) {
            Dotenv\Dotenv::createImmutable(APP_ROOT)->safeLoad();
        }

        $loaded = true;
    }
}

if (!function_exists('app_env')) {
    function app_env(string $key, ?string $default = null): ?string
    {
        app_load_env();

        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null || $value === '') {
            return $default;
        }

        return $value;
    }
}

if (!function_exists('app_cors_origin')) {
    function app_cors_origin(): ?string
    {
        $configuredOrigins = app_env('CORS_ALLOWED_ORIGINS', app_env('API_URL', '*'));
        $origins = array_filter(array_map('trim', explode(',', (string) $configuredOrigins)));
        $requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if (in_array('*', $origins, true)) {
            return '*';
        }

        if ($requestOrigin !== '' && in_array($requestOrigin, $origins, true)) {
            return $requestOrigin;
        }

        return $origins[0] ?? null;
    }
}

if (!function_exists('app_frontend_url')) {
    function app_frontend_url(): string
    {
        return rtrim((string) app_env('FRONTEND_URL', app_env('API_URL', 'http://localhost:3000')), '/');
    }
}
?>
