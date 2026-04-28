<?php
require_once __DIR__ . "/../../headers/headers.php";
require_once __DIR__ . "/../../config_db.php"; // Include database connection

define("SECRET_KEY", file_get_contents(__DIR__ . "/secret.key")); // Read the secret key


function validateToken() {

$headers = getallheaders();

    // Check if the Authorization header is present
    if (!isset($headers["Authorization"])) {
        http_response_code(401);
        echo json_encode(["error" => "No token provided"]);
    
        exit;
    }

    // Extract the token from the Authorization header
    $authHeader = $headers["Authorization"];
    if (strpos($authHeader, "Bearer ") !== 0) {
        http_response_code(401);
        echo json_encode(["error" => "Invalid token format"]);
        exit;
    }

    $token = str_replace("Bearer ", "", $authHeader);
    $tokenParts = explode(".", $token);

    if (count($tokenParts) !== 3) {
        http_response_code(401);
        echo json_encode(["error" => "Invalid token structure"]);
        exit;
    }

    $header = $tokenParts[0];
    $payload = $tokenParts[1];
    $signature = $tokenParts[2];

    // Decode the payload
    $decodedPayload = json_decode(base64_decode($payload), true);

    // Validate the signature
    $expectedSignature = rtrim(strtr(base64_encode(hash_hmac('sha256', $header . "." . $payload, SECRET_KEY, true)), '+/', '-_'), '=');

    if ($expectedSignature !== $signature) {
        http_response_code(401);
        echo json_encode(["error" => "Invalid token signature"]);
        exit;
    }

    // Validate the expiration time
    if (!isset($decodedPayload["exp"])) {
        http_response_code(401);
        echo json_encode(["error" => "Expiration time not set"]);
        exit;
    }

    if ($decodedPayload["exp"] < time()) {
        http_response_code(401);
        echo json_encode(["error" => "Token expired"]);
        exit;
    }

    // Return the decoded payload
    return $decodedPayload;

}

$isDirectScript = realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__);
$isRoutedTarget = ($GLOBALS['APP_DISPATCH_TARGET'] ?? null) === realpath(__FILE__);

if (($isDirectScript || $isRoutedTarget) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $decodedPayload = validateToken();
    echo json_encode(["message" => "Token valid", "user" => $decodedPayload]);
}
?>
