<?php

require_once __DIR__ . '/../../vendor/autoload.php'; // Include Composer's autoloader
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require "../../config_db.php"; // Include database connection
require "./secret.key"; // Include secret key

function authenticate() {
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        die(json_encode(["error" => "Unauthorized"]));
    }

    $token = str_replace("Bearer ", "", $headers['Authorization']);
   
    try {
        // Decode the JWT token
        $decoded = JWT::decode($token, new Key(file_get_contents("./secret.key"), 'HS256'));

        // Ensure the token contains a user_id
        if (!isset($decoded->user_id)) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token payload']);
            exit;
        }

        return $decoded;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid token: ' . $e->getMessage()]);
        exit;
    }
}


function isCustomer($user) {
    if ($user->role !== 'customer') {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied. Customer role required']);
        exit;
    }
    return true;
}

$conn->close();
?>

