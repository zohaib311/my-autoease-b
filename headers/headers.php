<?php
require_once __DIR__ . '/../includes/app_env.php';

$origin = app_cors_origin();

if ($origin !== null) {
    header("Access-Control-Allow-Origin: $origin");
}

header("Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($origin !== '*') {
    header("Access-Control-Allow-Credentials: true");
}

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
