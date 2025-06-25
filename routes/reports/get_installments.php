<?php
require_once '../../config_db.php';
header("Access-Control-Allow-Origin: http://localhost:3000"); // Allow requests from your frontend origin
header("Access-Control-Allow-Methods: POST, OPTIONS"); // Allow POST and OPTIONS methods
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allow specific headers
header("Access-Control-Allow-Credentials: true"); // Allow credentials (if needed)
header("Content-Type: application/json");

try {
    // Adjust the WHERE clause as needed for your logic (e.g., created_at in last X days)
    $result = $conn->query("SELECT id, order_id, installment_number, amount, due_date, status, created_at FROM installments ORDER BY created_at DESC");
    $installments = [];
    while ($row = $result->fetch_assoc()) {
        $installments[] = $row;
    }
    echo json_encode(["success" => true, "installments" => $installments]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>