<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// require "./../../../includes/cors.php"; // Include CORS headers
require_once __DIR__ . "/../../config_db.php"; // Include database connection
// require_once __DIR__ . "/../../includes/secret_key.php"; // Include secret key
require './validate_token.php';
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    global $conn;
    $decodedToken = validateToken();

    $user_id = $decodedToken['id'];
    $stmt = $conn->prepare("SELECT name, email, city, address, phone FROM customer_profiles WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $profile = $result->fetch_assoc();
        echo json_encode(["success" => true, "profile" => $profile]);
    } else {
        echo json_encode(["success" => false, "message" => "Profile not found"]);
    }

    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching profile: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "An error occurred: " . $e->getMessage()]);
}

$conn->close();
?>