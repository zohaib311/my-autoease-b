<?php
header("Access-Control-Allow-Origin: http://localhost:3000"); // Allow requests from your frontend origin
header("Access-Control-Allow-Methods: DELETE, OPTIONS"); // Allow DELETE and OPTIONS methods
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allow specific headers
header("Access-Control-Allow-Credentials: true"); // Allow credentials (if needed)
header("Content-Type: application/json");

require '../../../config_db.php'; // Include your database connection
require '../../../controller/auth/validate_token.php'; // Include authentication middleware

// Handle preflight (OPTIONS) requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); // Respond with HTTP 200 for preflight requests
    exit;
}

// Authenticate the user (you can use your token validation logic here)

$user = validateToken(); // Authenticate the user

// Get the cart_id from the query string
if (!isset($_GET['cart_id'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Cart ID is required"]);
    exit;
}

$cart_id = intval($_GET['cart_id']);
$user_id = validateToken()['id'];

try {
    global $conn;

    // Delete the cart item for the logged-in user
    $stmt = $conn->prepare("DELETE FROM carts WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cart_id, $user_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "Cart item deleted successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to delete cart item or item not found"]);
    }

    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "An error occurred: " . $e->getMessage()]);
}

$conn->close();
?>