<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 3600"); // Cache preflight response for 1 hour
header("Access-Control-Allow-Origin: http://localhost:3000"); // Replace with your frontend's origin
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");

require_once "../controller/auth/auth.php";
require_once "../config_db.php";

$user = authenticate(); // Authenticate the user
isCustomer($user); // Ensure the user has the "customer" role

$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (!isset($data['car_id']) || !isset($data['total_amount']) || !isset($data['user_name']) || !isset($data['user_email']) || !isset($data['user_phone']) || !isset($data['delivery_address'])) {
    echo json_encode(["error" => "All fields are required"]);
    exit;
}

// Extract data from the request
$car_id = $data['car_id'];
$total_amount = $data['total_amount'];
$user_name = $data['user_name'];
$user_email = $data['user_email'];
$user_phone = $data['user_phone'];
$delivery_city = $data['delivery_city'] ?? '';
$delivery_address = $data['delivery_address'];
$payment_method = $data['payment_method'] ?? 'cash_on_delivery';

try {
    global $conn;

    $stmt = $conn->prepare("
        INSERT INTO orders (user_id, car_id, total_amount, user_name, user_email, user_phone, delivery_city, delivery_address, payment_method)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "iisssssss",
        $user['id'],
        $car_id,
        $total_amount,
        $user_name,
        $user_email,
        $user_phone,
        $delivery_city,
        $delivery_address,
        $payment_method
    );

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Order placed successfully!"]);
    } else {
        error_log("Database Error: " . $conn->error);
        echo json_encode(["error" => "Failed to place order."]);
    }
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "An error occurred: " . $e->getMessage()]);
}
?>