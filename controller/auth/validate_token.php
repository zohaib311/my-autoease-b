<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

error_log("Authorization header: " . $headers["Authorization"]);
error_log("Payload: " . json_encode($payload));
error_log("Generated signature: " . $signature);

require "./secret.key"; // Ensure SECRET_KEY is defined in this file

$headers = getallheaders();

// Check if the Authorization header is present
if (!isset($headers["Authorization"])) {
    echo json_encode(["error" => "No token provided"]);
    exit;
}

// Extract the token from the Authorization header
$authHeader = $headers["Authorization"];
if (strpos($authHeader, "Bearer ") !== 0) {
    echo json_encode(["error" => "Invalid token format"]);
    exit;
}

$token = str_replace("Bearer ", "", $authHeader);
$tokenParts = explode(".", $token);

if (count($tokenParts) !== 2) {
    echo json_encode(["error" => "Invalid token structure"]);
    exit;
}

$payload = json_decode(base64_decode($tokenParts[0]), true);
$signature = base64_encode(hash_hmac('sha256', $tokenParts[0], SECRET_KEY, true));

// Validate the signature and expiration time
if ($signature !== $tokenParts[1]) {
    echo json_encode(["error" => "Invalid token signature"]);
    exit;
}

if ($payload["exp"] < time()) {
    echo json_encode(["error" => "Token expired"]);
    exit;
}

// Token is valid
echo json_encode(["message" => "Token valid", "user" => $payload]);
?>