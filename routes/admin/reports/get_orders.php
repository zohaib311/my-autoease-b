<?php
require_once __DIR__ . '/../../../config_db.php';
require_once __DIR__ . '/../../../headers/headers.php';

// header("Access-Control-Allow-Origin: http://localhost:3000"); // Allow requests from your frontend origin
// header("Access-Control-Allow-Methods: POST, OPTIONS"); // Allow POST and OPTIONS methods
// header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allow specific headers
// header("Access-Control-Allow-Credentials: true"); // Allow credentials (if needed)
// header("Content-Type: application/json");

try {
    // Adjust field names as per your orders table
    $result = $conn->query("SELECT id, name, email, phone, city, address, status, total_amount, created_at AS date FROM orders ORDER BY created_at DESC");
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    echo json_encode(["success" => true, "orders" => $orders]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
