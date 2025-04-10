<?php

require "../../vendor/autoload.php"; // Include Composer's autoloader
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require "../config_db.php"; // Include database connection
require "./secret.key"; // Include secret key

function authenticate() {
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        die(json_encode(["error" => "Unauthorized"]));
    }

    $token = str_replace("Bearer ", "", $headers['Authorization']);
    try {
        $decoded = JWT::decode($token, new Key(file_get_contents("./secret.key"), 'HS256'));
        return $decoded->user_id;
    } catch (Exception $e) {
        http_response_code(401);
        die(json_encode(["error" => "Invalid token"]));
    }
}

$conn->close();
?>