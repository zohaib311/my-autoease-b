<?php
require_once __DIR__ . "/../../headers/headers.php";

include_once __DIR__ . "/../../vendor/autoload.php"; // Include Composer's autoloader
use Firebase\JWT\JWT;
include __DIR__ . "/../../config_db.php";

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->email) || !isset($data->password)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid input"]);
    exit;
}

$email = $conn->real_escape_string($data->email);
$password = $data->password;

// Fetch user from database
$sql = "SELECT * FROM users WHERE email='$email'";
$result = $conn->query($sql);

if ($result === false) {
    http_response_code(500);
    echo json_encode(["error" => "Login query failed"]);
    $conn->close();
    exit;
}

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        $payload = [
            "id" => $user["id"],
            "email" => $user["email"],
            "role" => $user["role"], // Include role in the payload
            "exp" => time() + 3600 // Token expires in 1 hour
        ];

        $secretKey = app_jwt_secret();
        $jwt = JWT::encode($payload, $secretKey, 'HS256'); // Generate the JWT token

        echo json_encode([
            "token" => $jwt,
            "role" => $user["role"] // Return role in the response
        ]);
    } else {
        echo json_encode(["error" => "Invalid password"]);
    }
} else {
    echo json_encode(["error" => "Incorrect email"]);
}

$conn->close();
?>
