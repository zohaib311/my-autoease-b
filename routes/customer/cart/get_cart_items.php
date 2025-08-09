<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

require '../../../config_db.php';
require '../../../controller/auth/validate_token.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $user = validateToken();

    if ($user['role'] !== 'customer') {
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "message" => "Access denied. Only customers can access this resource."
        ]);
        exit;
    }

    $user_id = $user['id'];

    $stmt = $conn->prepare("
        SELECT 
            cars.id,
            cars.name,
            cars.price,
            cars.year,
            cars.fuel_type,
            cars.manufacturer,
            cars.image,
            cars.available_city,
            carts.id AS cart_id
        FROM carts
        JOIN cars ON carts.car_id = cars.id
        WHERE carts.user_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();

    $cars = [];

    while ($row = $result->fetch_assoc()) {
        $cars[] = $row;
    }

    echo json_encode([
        "success" => true,
        "cart_items" => $cars
    ]);

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "An error occurred: " . $e->getMessage()
    ]);
}
?>