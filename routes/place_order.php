<?php
header("Access-Control-Allow-Origin: http://localhost:3000"); // Allow requests from your frontend origin
header("Access-Control-Allow-Methods: POST, OPTIONS"); // Allow POST and OPTIONS methods
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allow specific headers
header("Access-Control-Allow-Credentials: true"); // Allow credentials (if needed)
header("Content-Type: application/json");

require '../config_db.php'; // Include your database connection
require '../controller/auth/validate_token.php'; // Include authentication middleware

// Handle preflight (OPTIONS) requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$user = validateToken();
$user_id = $user['id']; // Authenticate the user
// isCustomer($user); // Ensure the user has the "customer" role
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($data['car_id'], $data['payment_method'], $data['delivery_charges'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Car ID, payment method, and delivery charges are required"]);
    exit;
}

$car_id = $data['car_id'];
$payment_method = $data['payment_method'];
$delivery_charges = $data['delivery_charges'] ?? 10000; // Default delivery charges
$notes = $data['notes'] ?? null;

try {
    global $conn;

    // Fetch customer profile
    $stmt = $conn->prepare("SELECT name, email, phone, city, address FROM customer_profiles WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Customer profile not found"]);
        exit;
    }

    $profile = $result->fetch_assoc();
    $name = $profile['name'];
    $email = $profile['email'];
    $phone = $profile['phone'];
    $city = $profile['city'];
    $address = $profile['address'];

    // Calculate total amount
    $stmt = $conn->prepare("SELECT price FROM cars WHERE id = ?");
    $stmt->bind_param("i", $car_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Car not found"]);
        exit;
    }

    $car = $result->fetch_assoc();
    $car_price = $car['price'];
    $total_amount = $car_price + $delivery_charges;

    // Insert order into the database
    $stmt = $conn->prepare("
        INSERT INTO orders (user_id, car_id, name, email, phone, city, address, delivery_charges, total_amount, payment_method, notes, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "iissssssddss",
        $user_id,
        $car_id,
        $name,
        $email,
        $phone,
        $city,
        $address,
        $delivery_charges,
        $total_amount,
        $payment_method,
        $notes,
        $status
    );

    $status = 'pending'; // Default status

    if ($stmt->execute()) {
        // Get the last inserted order ID
        $order_id = $conn->insert_id;
    
        echo json_encode([
            "success" => true,
            "message" => "Order placed successfully",
            "order_id" => $order_id // Include the order ID in the response
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Failed to place order"
        ]);
    }

    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "An error occurred: " . $e->getMessage()]);
}

$conn->close();