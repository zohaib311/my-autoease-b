<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require '../config_db.php'; // Include your database connection file
require '../controller/auth/validate_token.php'; // Include your token validation file

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $data["user_id"];
$car_id = $data["car_id"];

if (!$user_id || !$car_id) {
    echo json_encode(["error" => "Missing user_id or car_id"]);
    exit;
}

// Validate user_id
$stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["error" => "Invalid user_id. User does not exist."]);
    exit;
}

$stmt->close();

// Insert into cart
$stmt = $conn->prepare("INSERT INTO cart (user_id, car_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $car_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Car added to cart in database"]);
} else {
    echo json_encode(["error" => "Database insert failed"]);
}

$stmt->close();
$conn->close();
?>