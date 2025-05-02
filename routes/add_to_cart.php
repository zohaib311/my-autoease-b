<?php
// CORS headers
header("Access-Control-Allow-Origin: http://localhost:3000"); // Allow requests from your frontend origin
header("Access-Control-Allow-Methods: POST, GET, OPTIONS"); // Allow specific HTTP methods
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allow specific headers
header("Access-Control-Allow-Credentials: true"); // Allow credentials (if needed)

// Handle preflight (OPTIONS) requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); // Respond with HTTP 200 for preflight requests
    exit;
}

// Include your database connection
require '../config_db.php';
// require '../controller/auth/validate_token.php'; // Uncomment if using token validation
// require '../controller/auth/auth_middleware.php'; // Uncomment if using token validation
// require '../controller/auth/auth.php'; // Uncomment if using token validation

// Read input data
$input = file_get_contents("php://input");
$data = json_decode($input);

// Check if data is valid
if (!isset($data->cart_items->id)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid input: Car ID is missing."]);
    exit;
}

// Sample user_id (later replace it with user_id from token)
// $user_id = $decoded_token->user_id;

$user_id = 10;
// $user_id = $data->user_id; // For testing purposes, replace with actual user ID from authentication
$car_id = $data->cart_items->id;

// Check if this car is already added for this user
$checkStmt = $conn->prepare("SELECT id FROM carts WHERE user_id = ? AND car_id = ?");
$checkStmt->bind_param("ii", $user_id, $car_id);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    // Duplicate found
    http_response_code(409); // Conflict
    echo json_encode(["success" => false, "message" => "Duplicate entry: Car already in cart."]);
} else {
    // No duplicate, insert
    $insertStmt = $conn->prepare("INSERT INTO carts (user_id, car_id) VALUES (?, ?)");
    if ($insertStmt === false) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Database error: Prepare failed."]);
        exit;
    }
    $insertStmt->bind_param("ii", $user_id, $car_id);

    if ($insertStmt->execute()) {
        http_response_code(201);
        echo json_encode(["success" => true, "message" => "Car added to cart successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Database error while inserting."]);
    }
    $insertStmt->close();
}

// Close resources
$checkStmt->close();
$conn->close();
?>
