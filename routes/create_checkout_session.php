<?php
require_once '../vendor/autoload.php';
require_once '../config_db.php'; // Include your database connection
require_once '../controller/auth/validate_token.php'; // Include authentication middleware

\Stripe\Stripe::setApiKey('sk_test_51RJgLmQpF59V7kiLqlA1zyl2Loj2cxnSDZmnTcixZYzCDLO8TZzJg5vF3KpmQvG4QB67zzb3bjE0XcC6UVBgS2eI008RIlUpfj'); // Replace with your Stripe Secret Key

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Authenticate the user and fetch the user ID from the token
try {
    $user = validateToken(); // This function should decode the token and return user details
    $user_id = $user['id']; // Extract user_id from the decoded token
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized: " . $e->getMessage()]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['amount'], $data['car_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Amount and car ID are required"]);
    exit;
}

$amount = $data['amount'];
$car_id = $data['car_id'];

try {
    // Create a Stripe Checkout session
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'pkr',
                'product_data' => [
                    'name' => "Car Purchase (Car ID: $car_id)",
                ],
                'unit_amount' => $amount * 100, // Amount in paisa
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => 'http://localhost:3000/car-list/car-detail/car/' . $car_id . '/place-order/order-Success?session_id={CHECKOUT_SESSION_ID}', // Redirect after success
        'cancel_url' => 'http://localhost:3000/car-list/car-detail/car/' . $car_id . '/place-order/order-cancel', // Redirect after cancellation
    ]);

    echo json_encode(['id' => $session->id]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>