
<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS");
header("Content-Type: application/json");

require_once '../controller/auth/authMiddleware.php';
require_once '../config_db.php';

$user = authenticate();
isCustomer($user);


switch ($method) {
    case 'GET':
        // Get user's cart
        $stmt = $conn->prepare("
            SELECT c.*, cars.model, cars.price, cars.image 
            FROM cart c
            JOIN cars ON c.car_id = cars.id
            WHERE c.user_id = ?
        ");
        $stmt->bind_param("i", $user['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $cart = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($cart);
        break;

    case 'POST':
        // Add to cart
        $data = json_decode(file_get_contents('php://input'), true);
        $carId = $data['car_id'];
        $quantity = $data['quantity'] ?? 1;

        // Check if already in cart
        $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND car_id = ?");
        $stmt->bind_param("ii", $user['id'], $carId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update quantity
            $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND car_id = ?");
            $stmt->bind_param("iii", $quantity, $user['id'], $carId);
        } else {
            // Add new item
            $stmt = $conn->prepare("INSERT INTO cart (user_id, car_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $user['id'], $carId, $quantity);
        }
        
        $stmt->execute();
        echo json_encode(['success' => true]);
        break;

    case 'DELETE':
        // Remove from cart
        $carId = $_GET['car_id'];
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND car_id = ?");
        $stmt->bind_param("ii", $user['id'], $carId);
        $stmt->execute();
        echo json_encode(['success' => true]);
        break;
}
?>