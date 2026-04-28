<?php

function app_load_env(): void
{
    static $loaded = false;

    if ($loaded) {
        return;
    }

    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($autoload)) {
        require_once $autoload;
    }

    $envPath = dirname(__DIR__);
    if (class_exists(Dotenv\Dotenv::class) && file_exists($envPath . '/.env')) {
        Dotenv\Dotenv::createImmutable($envPath)->safeLoad();
    }

    $loaded = true;
}

function app_env(string $key, ?string $default = null): ?string
{
    app_load_env();

    if (array_key_exists($key, $_ENV) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }

    if (array_key_exists($key, $_SERVER) && $_SERVER[$key] !== '') {
        return $_SERVER[$key];
    }

    $value = getenv($key);
    if ($value !== false && $value !== '') {
        return $value;
    }

    return $default;
}

function app_first_env(array $keys, ?string $default = null): ?string
{
    foreach ($keys as $key) {
        $value = app_env($key);
        if ($value !== null && $value !== '') {
            return $value;
        }
    }

    return $default;
}

function app_cors_origin(): string
{
    $configured = app_first_env(['CORS_ALLOWED_ORIGINS', 'FRONTEND_URL', 'API_URL'], '*');
    $origins = array_values(array_filter(array_map('trim', explode(',', $configured))));
    $requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';

    if (in_array('*', $origins, true)) {
        return '*';
    }

    if ($requestOrigin !== '' && in_array($requestOrigin, $origins, true)) {
        return $requestOrigin;
    }

    return $origins[0] ?? '*';
}

function app_send_cors_headers(): void
{
    $origin = app_cors_origin();

    header("Access-Control-Allow-Origin: {$origin}");
    header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Content-Type: application/json');

    if ($origin !== '*') {
        header('Access-Control-Allow-Credentials: true');
    }
}

function app_jwt_secret(): string
{
    if (defined('SECRET_KEY')) {
        return (string) SECRET_KEY;
    }

    $secret = app_env('JWT_SECRET');
    if ($secret !== null && $secret !== '') {
        return $secret;
    }

    $secretFile = __DIR__ . '/../controller/auth/secret.key';
    if (file_exists($secretFile)) {
        ob_start();
        include $secretFile;
        ob_end_clean();

        if (defined('SECRET_KEY')) {
            return (string) SECRET_KEY;
        }
    }

    throw new RuntimeException('JWT_SECRET is not configured.');
}
