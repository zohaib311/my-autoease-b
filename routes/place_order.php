<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require_once "../controller/auth/auth_middleware.php";
require_once "../config_db.php"; // Ensure this file contains the global $conn variable

$user = authenticate(); // Authenticate the user
isCustomer($user); // Ensure the user has the "customer" role

$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (!isset($data['car_id']) || !isset($data['total_amount']) || !isset($data['full_name']) || !isset($data['email']) || !isset($data['phone']) || !isset($data['delivery_address'])) {
    echo json_encode(["error" => "All fields are required"]);
    exit;
}

// Extract data from the request
$car_id = $data['car_id'];
$total_amount = $data['total_amount'];
$full_name = $data['full_name'];
$email = $data['email'];
$phone = $data['phone'];
$delivery_city = $data['delivery_city'] ?? '';
$delivery_address = $data['delivery_address'];
$delivery_charges = $data['delivery_charges'] ?? 0;
$delivery_date = $data['delivery_date'] ?? date('Y-m-d');
$postal = $data['postal'] ?? '';
$notes = $data['notes'] ?? '';
$payment_method = $data['payment_method'] ?? 'cash_on_delivery';

try {
    // Use the existing database connection ($conn)
    global $conn;

    $stmt = $conn->prepare("
        INSERT INTO orders (user_id, car_id, total_amount, full_name, email, phone, delivery_city, delivery_address, delivery_charges, delivery_date, postal, notes, payment_method)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "iisssssssssss",
        $user['id'],
        $car_id,
        $total_amount,
        $full_name,
        $email,
        $phone,
        $delivery_city,
        $delivery_address,
        $delivery_charges,
        $delivery_date,
        $postal,
        $notes,
        $payment_method
    );

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Order placed successfully!"]);
    } else {
        echo json_encode(["error" => "Failed to place order."]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "An error occurred: " . $e->getMessage()]);
}
?>