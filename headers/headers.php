<?php
require_once __DIR__ . '/../includes/app_env.php';

app_send_cors_headers();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
