<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include "../../config_db.php";

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->username) || !isset($data->email) || !isset($data->password) || !isset($data->role)) {
    echo json_encode(["error" => "All fields are required"]);
    exit;
}

$username = $conn->real_escape_string($data->username);
$email = $conn->real_escape_string($data->email);
$password = password_hash($data->password, PASSWORD_DEFAULT); // Hash password
$role = $conn->real_escape_string($data->role);

// Validate role
if (!in_array($role, ['admin', 'customer'])) {
    echo json_encode(["error" => "Invalid role"]);
    exit;
}

// Check if email already exists
$sql = "SELECT * FROM users WHERE email='$email'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    echo json_encode(["error" => "Email already registered"]);
    exit;
}

// Insert user into database
$sql = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$password', '$role')";
if ($conn->query($sql) === TRUE) {
    echo json_encode([
        "success" => "User registered successfully",
        "role" => $role // Return the role in the response
    ]);
} else {
    echo json_encode(["error" => "Registration failed"]);
}

$conn->close();
?>