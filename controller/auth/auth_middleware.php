<?php
require_once __DIR__ . '/../../vendor/autoload.php'; // Include the JWT library
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Define your secret key (ensure this is stored securely)
define("SECRET_KEY", "mysecretkey12345");

function authenticate() {
    $headers = getallheaders();

    // Check if the Authorization header is present
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Authorization token required']);
        exit;
    }

    // Extract the token from the Authorization header
    $token = str_replace('Bearer ', '', $headers['Authorization']);

    try {
        // Decode the JWT token
        $decoded = JWT::decode($token, new Key(SECRET_KEY, 'HS256'));

        // Ensure the token contains a user_id
        if (!isset($decoded->user_id)) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token payload']);
            exit;
        }

        // Return the decoded token as an associative array
        return (array) $decoded;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid token: ' . $e->getMessage()]);
        exit;
    }
}

function isCustomer($user) {
    // Check if the user's role is 'customer'
    if (!isset($user['role']) || $user['role'] !== 'customer') {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied. Customer role required']);
        exit;
    }
    return true;
}