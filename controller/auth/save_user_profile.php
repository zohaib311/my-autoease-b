<?php
require_once __DIR__ . "/../../headers/headers.php";

require __DIR__ . '/validate_token.php'; // Include authentication middleware


// Handle preflight (OPTIONS) requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); // Respond with HTTP 200 for preflight requests
    exit;
}

require __DIR__ . '/../../config_db.php'; // Include your database connection

$user = validateToken(); // Authenticate the user
// isCustomer($user); // Ensure the user has the "customer" role

$data = json_decode(file_get_contents("php://input"), true); // Move this line up to ensure $data is available before accessing its properties


// Validate input
if (!isset($data['city'], $data['phone'], $data['address'], $data['name'], $data['email'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "City, phone, address, name, and email are required"]);
    exit;
}


$user_id = $user['id'];
$email = $data['email'];
$name = $data['name'];
$city = $data['city'];
$phone = $data['phone'];
$address = $data['address'];

try {
    global $conn;

    // Check if the user profile already exists
    $stmt = $conn->prepare("SELECT id FROM customer_profiles WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing profile
        $stmt = $conn->prepare("UPDATE customer_profiles SET name = ?, email = ?, city = ?, address = ?, phone = ? WHERE user_id = ?");
        $stmt->bind_param("ssssis", $name, $email, $city, $address, $phone, $user_id);
    } else {
        // Insert new profile
        $stmt = $conn->prepare("INSERT INTO customer_profiles (user_id, name, email, city, address, phone) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $user_id, $name, $email, $city, $address, $phone);
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
