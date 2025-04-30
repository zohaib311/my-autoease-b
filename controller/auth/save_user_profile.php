<?php
// CORS headers
header("Access-Control-Allow-Origin: http://localhost:3000"); // Allow requests from your frontend origin
header("Access-Control-Allow-Methods: POST, OPTIONS"); // Allow POST and OPTIONS methods
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allow specific headers
header("Access-Control-Allow-Credentials: true"); // Allow credentials (if needed)
header("Content-Type: application/json");

// Handle preflight (OPTIONS) requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); // Respond with HTTP 200 for preflight requests
    exit;
}

require '../../config_db.php'; // Include your database connection
// require '../auth/auth_middleware.php'; // Include authentication middleware

// $user = authenticate(); // Authenticate the user
// isCustomer($user); // Ensure the user has the "customer" role

$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($data['city'], $data['phone'], $data['address'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "City, phone, and address are required"]);
    exit;
}

$city = $data['city'];
$phone = $data['phone'];
$address = $data['address'];
// $email = $user['email']; // Get email from the authenticated user
// $name = $user['name']; // Get name from the authenticated user
// $user_id = $user['user_id']; // Get user ID from the authenticated user
$email = 'asim@gmail.com'; // Get email from the authenticated user
$name = 'Asim'; // Get name from the authenticated user
$user_id = 10; // Get user ID from the authenticated user

try {
    global $conn;

    // Check if the user profile already exists
    $stmt = $conn->prepare("SELECT id FROM customer_profiles WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing profile
        $stmt = $conn->prepare("UPDATE customer_profiles SET name = ?, email = ?, city = ?, phone = ?, address = ? WHERE user_id = ?");
        $stmt->bind_param("ssssis", $name, $email, $city, $phone, $address, $user_id);
    } else {
        // Insert new profile
        $stmt = $conn->prepare("INSERT INTO customer_profiles (user_id, name, email, city, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $user_id, $name, $email, $city, $phone, $address);
    }

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Profile saved successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to save profile"]);
    }

    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "An error occurred: " . $e->getMessage()]);
}

$conn->close();
?>