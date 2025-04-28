<?php
// // CORS headers
// header("Access-Control-Allow-Origin: http://localhost:3000"); // Allow requests from your frontend origin
// header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE"); // Allow specific HTTP methods
// header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allow specific headers
// header("Access-Control-Allow-Credentials: true"); // Allow credentials (if needed)

// Handle preflight (OPTIONS) requests
// if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
//     http_response_code(200); // Respond with HTTP 200 for preflight requests
//     exit;
// }

// // Include your database connection and authentication middleware
// require '../config_db.php';
// require '../controller/auth/auth_middleware.php';

// $user = authenticate(); // Authenticate the user
// isCustomer($user); // Ensure the user has the "customer" role

// $user_id = $user['user_id']; // Get the logged-in user's ID
// $user_id = 10;

// try {
//     global $conn;

    // Fetch all cart items for the logged-in user
//     $stmt = $conn->prepare("
//         SELECT c.id AS cart_id, c.car_id 
//         FROM carts c
//         JOIN cars ON c.car_id = cars.id
//         WHERE c.user_id = ?
//     ");
//     $stmt->bind_param("i", $user_id);
//     $stmt->execute();
//     $result = $stmt->get_result();

//     $cart_items = [];
//     while ($row = $result->fetch_assoc()) {
//         $cart_items[] = $row;
//     }

//     echo json_encode(["success" => true, "cart_items" => $cart_items]);
// } catch (Exception $e) {
//     http_response_code(500);
//     echo json_encode(["success" => false, "message" => "Failed to fetch cart items: " . $e->getMessage()]);
// }
?>


<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
// CORS headers
header("Access-Control-Allow-Origin: http://localhost:3000"); // Allow requests from your frontend origin
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE"); // Allow specific HTTP methods
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allow specific headers
header("Access-Control-Allow-Credentials: true"); // Allow credentials (if needed)

require '../config_db.php'; // Your database connection


// Handle preflight (OPTIONS) requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); // Respond with HTTP 200 for preflight requests
    exit;
}
// Assume user ID is known (later take from token)
$user_id = 10;

$stmt = $conn->prepare("
    SELECT 
        cars.id,
        cars.name,
        cars.price,
        cars.year,
        cars.fuel_type,
        cars.manufacturer,
        cars.image,
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
?>
