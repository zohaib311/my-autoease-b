<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require '../config_db.php';
require '../controller/auth/validate_token.php';

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
    $user = validateToken(); // Validate user token
    $user_id = $user['id']; // Get user ID from token

    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['order_id'], $data['status'])) {
        http_response_code(400); // Bad Request
        echo json_encode(["success" => false, "message" => "Order ID and status are required"]);
        exit;
    }

    $order_id = $data['order_id'];
    $status = $data['status'];

    // Check if the order belongs to the user and fetch its creation time
    $stmt = $conn->prepare("SELECT id, created_at FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Order not found or unauthorized"]);
        exit;
    }

    $order = $result->fetch_assoc();
    $created_at = new DateTime($order['created_at']);
    $current_time = new DateTime();
    $time_difference = $current_time->diff($created_at);

    // Check if the order was placed within the last 24 hours
    if ($time_difference->days > 0 || $time_difference->h > 24) {
        echo json_encode(["success" => false, "message" => "Order cannot be canceled after 24 hours"]);
        exit;
    }

    // Update the order status to "Canceled"
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