<?php

require_once __DIR__ . '../../../vendor/autoload.php'; // Include Composer's autoloader
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require_once __DIR__ . "/../../config_db.php"; // Use absolute path for database connection

// function authenticate() {
//     $headers = getallheaders();
//     if (!isset($headers['Authorization'])) {
//         http_response_code(401);
//         die(json_encode(["error" => "Unauthorized"]));
//     }

//     $token = str_replace("Bearer ", "", $headers['Authorization']);
//     $secretKey = file_get_contents(__DIR__ . "/secret.key"); // Read the secret key

//     try {
//         // Decode the JWT token
//         $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

//         // Ensure the token contains a user_id
//         if (!isset($decoded->user_id)) {
//             http_response_code(401);
//             echo json_encode(['error' => 'Invalid token payload']);
//             exit;
//         }

//         return $decoded;
//     } catch (Exception $e) {
//         http_response_code(401);
//         echo json_encode(['error' => 'Invalid token: ' . $e->getMessage()]);
//         exit;
//     }
// }
function authenticate() {
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        error_log("Authorization header missing");
        http_response_code(401);
        die(json_encode(["error" => "Unauthorized"]));
    }

    $token = str_replace("Bearer ", "", $headers['Authorization']);
    error_log("Extracted token: " . $token); // Debugging log

    $secretKey = file_get_contents(__DIR__ . "/secret.key");
    if (!$secretKey) {
        error_log("Secret key file not found");
        http_response_code(500);
        die(json_encode(["error" => "Secret key file not found"]));
    }

    try {
        $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
        if (!isset($decoded->user_id)) {
            error_log("Invalid token payload: user_id missing");
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token payload']);
            exit;
        }

        return $decoded;
    } catch (Exception $e) {
        error_log("Invalid token: " . $e->getMessage());
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
?>