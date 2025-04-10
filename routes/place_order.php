<?php
require "../config_db.php"; // Include database connection
require "../controller/auth/auth.php"; // Include JWT auth script

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"));

$user_id = authenticate(); // Get user ID from JWT
$car_id = $conn->real_escape_string($data->car_id);
$city = $conn->real_escape_string($data->delivery_city);
$address = $conn->real_escape_string($data->delivery_address);
$charges = (float)$data->delivery_charges;
$total = (float)$data->total_amount;
$method = $conn->real_escape_string($data->payment_method);
$notes = isset($data->notes) ? $conn->real_escape_string($data->notes) : "";

$sql = "INSERT INTO orders 
(user_id, car_id, delivery_city, delivery_address, delivery_charges, total_amount, payment_method, notes)
VALUES 
('$user_id', '$car_id', '$city', '$address', '$charges', '$total', '$method', '$notes')";

if ($conn->query($sql)) {
    echo json_encode(["message" => "Order placed successfully"]);
} else {
    echo json_encode(["error" => "Order failed: " . $conn->error]);
}
$conn->close();
?>