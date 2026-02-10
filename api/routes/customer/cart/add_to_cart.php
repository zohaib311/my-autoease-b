<?php
// CORS headers
require_once __DIR__ . '/../../../headers/headers.php';

// header("Access-Control-Allow-Origin: http://localhost:3000");
// header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
// header("Access-Control-Allow-Headers: Content-Type, Authorization");
// header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include your database connection and token validation
require '../../../config_db.php';
require '../../../controller/auth/validate_token.php'; // Include the validateToken function

// Validate the token and get the user details
try {
    $decodedToken = validateToken();
    $user_id = $decodedToken['id']; // Extract user_id from the decoded token
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Unauthorized: " . $e->getMessage()]);
    exit;
}

// Read input data
$input = file_get_contents("php://input");
$data = json_decode($input);

// Check if data is valid
if (!isset($data->cart_items->id)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid input: Car ID is missing."]);
    exit;
}

$car_id = $data->cart_items->id;

// Check if this car is already added for this user
$checkStmt = $conn->prepare("SELECT id FROM carts WHERE user_id = ? AND car_id = ?");
$checkStmt->bind_param("ii", $user_id, $car_id);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    http_response_code(409);
    echo json_encode(["success" => false, "message" => "Duplicate entry: Car already in cart."]);
} else {
    $insertStmt = $conn->prepare("INSERT INTO carts (user_id, car_id) VALUES (?, ?)");
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

$checkStmt->close();
$conn->close();
?>