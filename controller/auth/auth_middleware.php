<?php
require_once __DIR__ . '/../../vendor/autoload.php'; // Firebase JWT
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Define your secret key
define("SECRET_KEY", "mysecretkey12345");

function authenticateCustomer() {
    $headers = getallheaders();

    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Authorization header missing']);
        exit;
    }

    $token = str_replace('Bearer ', '', $headers['Authorization']);

    try {
        $decoded = JWT::decode($token, new Key(SECRET_KEY, 'HS256'));

        // Convert stdClass to array
        $user = json_decode(json_encode($decoded), true);

        if (!isset($user['role']) || $user['role'] !== 'customer') {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied. Customer role required.']);
            exit;
        }

        return $user;

    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid token: ' . $e->getMessage()]);
        exit;
    }
}
