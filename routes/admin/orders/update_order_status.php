<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require '../../../config_db.php';
require '../../../controller/auth/validate_token.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

try {
    $user = validateToken(); // Validate admin token
    if ($user['role'] !== 'admin') {
        http_response_code(403); // Forbidden
        echo json_encode(["success" => false, "message" => "Unauthorized access"]);
        exit;
    }

    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['order_id'], $data['status'])) {
        http_response_code(400); // Bad Request
        echo json_encode(["success" => false, "message" => "Order ID and status are required"]);
        exit;
    }

    $order_id = $data['order_id'];
    $status = $data['status'];

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Order status updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update order status"]);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "An error occurred: " . $e->getMessage()]);
}
?>