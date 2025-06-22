<?php
require_once '../../config_db.php';
require_once '../../controller/auth/validate_token.php';

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $user = validateToken();

    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($data['installment_id'], $data['status'])) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "installment_id and status are required"]);
        exit;
    }

    $installment_id = intval($data['installment_id']);
    $status = $data['status'];

    $stmt = $conn->prepare("UPDATE installments SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $installment_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update status"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>