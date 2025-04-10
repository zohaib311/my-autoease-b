<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include "../../config_db.php";
require "secret.key"; 

if (!defined('SECRET_KEY')) {
    define('SECRET_KEY', 'mysecretkey12345');
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->email) || !isset($data->password)) {
    echo json_encode(["error" => "Invalid input"]);
    exit;
}

$email = $conn->real_escape_string($data->email);
$password = $data->password;

// Fetch user from database
$sql = "SELECT * FROM users WHERE email='$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        $payload = [
            "id" => $user["id"],
            "email" => $user["email"],
            "role" => $user["role"], // Include role in the payload
            "exp" => time() + 3600 // Token expires in 1 hour
        ];

        $jwt = base64_encode(json_encode($payload)) . "." . base64_encode(hash_hmac('sha256', json_encode($payload), SECRET_KEY, true));
        
        echo json_encode([
            "token" => $jwt,
            "role" => $user["role"] // Return role in the response
        ]);
    } else {
        echo json_encode(["error" => "Invalid password"]);
    }
} else {
    echo json_encode(["error" => "User not found"]);
}

$conn->close();
?>