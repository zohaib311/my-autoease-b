<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

require '../config_db.php';
require '../controller/auth/validate_token.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

try {
    $user = validateToken();

    $stmt = $conn->prepare("
        SELECT 
            orders.id, 
            cars.name AS car_name, 
            orders.total_amount, 
            orders.payment_method,
            orders.status, 
            orders.created_at
        FROM orders
        JOIN cars ON orders.car_id = cars.id
        WHERE orders.user_id = ?
        ORDER BY orders.created_at DESC
    ");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();

    $result = $stmt->get_result();
    $orders = [];

    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }

    echo json_encode(["success" => true, "orders" => $orders]);

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "An error occurred: " . $e->getMessage()]);
}
?>