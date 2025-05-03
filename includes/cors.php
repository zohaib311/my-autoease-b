<?php
// filepath: d:\code-playground\react\auto-ease-app\my-autoease-b\includes\cors.php

// Allow requests from your frontend's origin
header("Access-Control-Allow-Origin: http://localhost:3000");

// Allow specific HTTP methods
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE");

// Allow specific headers
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Allow credentials (cookies, authorization headers, etc.)
header("Access-Control-Allow-Credentials: true");

// Handle preflight (OPTIONS) requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
?>