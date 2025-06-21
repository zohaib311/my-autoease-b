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

if (!isset($data['amount'], $data['car_id'], $data['order_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Amount, car ID, and order ID are required"]);
    exit;
}

$amount = $data['amount'];
$car_id = $data['car_id'];
$order_id = $data['order_id'];

try {
    // Create a Stripe Checkout session
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'pkr',
                'product_data' => [
                    'name' => "Car Purchase (Order ID: $order_id)",
                ],
                'unit_amount' => $amount * 100, // Amount in paisa
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => 'http://localhost:3000/car-list/car-detail/car/' . $car_id . '/place-order/order-Success?session_id={CHECKOUT_SESSION_ID}', // Redirect after success
        'cancel_url' => 'http://localhost:3000/car-list/car-detail/car/' . $car_id . '/place-order/order-cancel', // Redirect after cancellation
    ]);

    
    // $paymentIntent = \Stripe\PaymentIntent::create([
    //     'amount' => $data['amount'] * 100, // Amount in cents
    //     'currency' => 'pkr',
    //     'payment_method_types' => ['card'],
    //     'description' => 'Payment for order',
    // ]);
    // // Retrieve the payment intent ID
    // $payment_intent_id = $paymentIntent->id ?? null;

    // if (!$payment_intent_id) {
    //     throw new Exception("Payment intent ID is missing from the Stripe session.");
    // }

    // // Save payment details in the database
    // $stmt = $conn->prepare("INSERT INTO payment_details (user_id, order_id, amount, payment_intent_id, status) VALUES (?, ?, ?, ?, ?)");
    // $status = 'pending'; // Default status
    // $stmt->bind_param("iidss", $user_id, $order_id, $amount, $payment_intent_id, $status);

    // if ($stmt->execute()) {
    //     echo json_encode(['id' => $session->id, 'payment_intent_id' => $payment_intent_id, 'message' => 'Checkout session created and payment details saved successfully']);
    // } else {
    //     throw new Exception("Failed to save payment details in the database");
    // }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>