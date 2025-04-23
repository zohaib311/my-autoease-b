<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once '../controller/auth/auth.php';
require '../controller/auth/validate_token.php'; // Include your token validation file


// Authenticate user
$user = authenticate();
isCustomer($user);

require '../controller/auth/validate_token.php';

$data = json_decode(file_get_contents('php://input'), true);
$cartItems = $data['cart_items'] ?? [];

// Begin transaction
$conn->begin_transaction();

try {
    // Clear existing cart items for this user
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    
    // Insert new cart items
    $stmt = $conn->prepare("INSERT INTO cart (user_id, car_id, quantity) VALUES (?, ?, ?)");
    
    foreach ($cartItems as $item) {
        // Validate car exists
        $checkStmt = $conn->prepare("SELECT id FROM cars WHERE id = ?");
        $checkStmt->bind_param("i", $item['car_id']);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows === 0) {
            throw new Exception("Invalid car ID: " . $item['car_id']);
        }
        
        $stmt->bind_param("iii", $user['id'], $item['car_id'], $item['quantity']);
        $stmt->execute();
    }
    
    $conn->commit();
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>