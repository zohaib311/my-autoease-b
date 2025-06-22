<?php
require_once '../../config_db.php';
require_once '../../controller/auth/validate_token.php';

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $user = validateToken();
    // Admin can view any payment, so no user_id check here

    if (!isset($_GET['order_id'])) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "order_id is required"]);
        exit;
    }

    $order_id = intval($_GET['order_id']);

    $stmt = $conn->prepare("SELECT * FROM payment_details WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $payments = [];
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }

    if (count($payments) > 0) {
        echo json_encode(["success" => true, "payments" => $payments]);
    } else {
        echo json_encode(["success" => false, "message" => "No payments found for this order"]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>