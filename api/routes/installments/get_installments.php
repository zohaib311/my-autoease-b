<?php
require_once __DIR__ . '/../../headers/headers.php';

// header("Access-Control-Allow-Origin: http://localhost:3000");
// header("Access-Control-Allow-Methods: GET, OPTIONS"); 
      
require '../../config_db.php';
require '../../controller/auth/validate_token.php';
try {
    $user = validateToken(); // Validate user token

    if (!isset($_GET['order_id'])) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Order ID is required"]);
        exit;
    }

    $order_id = intval($_GET['order_id']);

    $stmt = $conn->prepare("SELECT * FROM installments WHERE order_id = ? ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $installments = [];
    while ($row = $result->fetch_assoc()) {
        $installments[] = $row;
    }

    if (empty($installments)) {
        echo json_encode(["success" => false, "message" => "No installments found for this order ID."]);
    } else {
        echo json_encode(["success" => true, "installments" => $installments]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "An error occurred: " . $e->getMessage()]);
}
?>