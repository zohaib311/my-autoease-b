<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require "secret.key";

$headers = getallheaders();
if (!isset($headers["Authorization"])) {
    echo json_encode(["error" => "No token provided"]);
    exit;
}

$token = explode(".", $headers["Authorization"]);
$payload = json_decode(base64_decode($token[0]), true);
$signature = base64_encode(hash_hmac('sha256', json_encode($payload), SECRET_KEY, true));

if ($signature !== $token[1] || $payload["exp"] < time()) {
    echo json_encode(["error" => "Invalid or expired token"]);
} else {
    echo json_encode(["message" => "Token valid", "user" => $payload]);
}
?>
