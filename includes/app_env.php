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

        app_load_php_config(APP_ROOT . '/includes/server_config.php');

        if (class_exists(Dotenv\Dotenv::class)) {
            Dotenv\Dotenv::createImmutable(APP_ROOT)->safeLoad();
        }

        app_load_env_file(APP_ROOT . '/.env');

        $loaded = true;
    }
}

if (!function_exists('app_set_env')) {
    function app_set_env(string $key, ?string $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv($key . '=' . $value);
    }
}

if (!function_exists('app_load_php_config')) {
    function app_load_php_config(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        $config = require $path;

        if (!is_array($config)) {
            return;
        }

        foreach ($config as $key => $value) {
            app_set_env((string) $key, is_scalar($value) ? (string) $value : null);
        }
    }
}

if (!function_exists('app_load_env_file')) {
    function app_load_env_file(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            if ($key === '') {
                continue;
            }

            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            app_set_env($key, $value);
        }
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
