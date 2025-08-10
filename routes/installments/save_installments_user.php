<?php
require_once __DIR__ . '/../../headers/headers.php';

// header("Access-Control-Allow-Origin: http://localhost:3000");
// header("Access-Control-Allow-Methods: POST, OPTIONS");
// header("Access-Control-Allow-Headers: Content-Type, Authorization");
// header("Content-Type: application/json");

require '../../config_db.php';
require '../../controller/auth/validate_token.php';

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
    $user = validateToken(); 
    $user_id = $user['id']; 
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['installments'], $data['order_id'], $data['car_id'])) {
        http_response_code(400); // Bad Request
        echo json_encode(["success" => false, "message" => "Missing required fields"]);
        exit;
    }

    $installments = $data['installments'];
    $order_id = $data['order_id'];
    $car_id = $data['car_id'];

    foreach ($installments as $installment) {
        $due_date = date('Y-m-d', strtotime($installment['dueDate']));
        $stmt = $conn->prepare("INSERT INTO installments (order_id, car_id, installment_number, amount, due_date, user_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiidss", $order_id, $car_id, $installment['installmentNumber'], $installment['amount'], $due_date, $user_id);
        $stmt->execute();
    }



    error_log("Installments saved for car_id: $car_id by user_id: $user_id");

    echo json_encode(["success" => true, "message" => "Installments saved successfully"]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "An error occurred: " . $e->getMessage()]);
}
?>