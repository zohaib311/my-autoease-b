<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include "../../config_db.php";
require "secret.key"; // Secret key file

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->username) || !isset($data->password)) {
    echo json_encode(["error" => "Invalid input"]);
    exit;
}

$username = $conn->real_escape_string($data->username);
$password = $data->password;

// Fetch user from database
$sql = "SELECT * FROM users WHERE username='$username'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        $payload = [
            "id" => $user["id"],
            "username" => $user["username"],
            "exp" => time() + 3600 // Token expires in 1 hour
        ];

        $jwt = base64_encode(json_encode($payload)) . "." . base64_encode(hash_hmac('sha256', json_encode($payload), SECRET_KEY, true));
        
        echo json_encode(["token" => $jwt]);
    } else {
        echo json_encode(["error" => "Invalid password"]);
    }
} else {
    echo json_encode(["error" => "User not found"]);
}
?>
