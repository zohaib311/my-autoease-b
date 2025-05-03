<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require '../../config_db.php';
require '../auth/validate_token.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

try {
    $user = validateToken();

    // Check if the user is an admin
    if ($user['role'] !== 'admin') {
        http_response_code(403); // Forbidden
        echo json_encode(["success" => false, "message" => "Unauthorized access"]);
        exit;
    }

    // Fetch all users
    $stmt = $conn->prepare("SELECT id, username, email, role FROM users ORDER BY id ASC ");
    $stmt->execute();

    $result = $stmt->get_result();
    $users = [];

    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    echo json_encode(["success" => true, "users" => $users]);

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "An error occurred: " . $e->getMessage()]);
}
?>