<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require '../../config_db.php';
require '../../controller/auth/validate_token.php';

try {
    $user = validateToken(); // Validate user token
    $user_id = $user['id']; // Get user ID from token
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['installment_id'], $data['status'])) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Invalid request data"]);
        exit;
    }

    $installment_id = intval($data['installment_id']);
    $status = $data['status'];

    $stmt = $conn->prepare("UPDATE installments SET status = ?, payment_id = UUID() WHERE id = ?");
    $stmt->bind_param("si", $status, $installment_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Installment status updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update installment status"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "An error occurred: " . $e->getMessage()]);
}