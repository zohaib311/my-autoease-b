<?php
require_once '../vendor/autoload.php';
require_once '../config_db.php'; // Include your database connection
// require '../controller/auth/auth.php'; // Include authentication middleware


\Stripe\Stripe::setApiKey('sk_test_51RJgLmQpF59V7kiLqlA1zyl2Loj2cxnSDZmnTcixZYzCDLO8TZzJg5vF3KpmQvG4QB67zzb3bjE0XcC6UVBgS2eI008RIlUpfj'); // Replace with your Stripe Secret Key

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");



if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// $user = authenticate(); // Authenticate the user
// isCustomer($user); // Ensure the user has the "customer" role

// $user_id = $user['user_id']; // Get the logged-in user's ID

$data = json_decode(file_get_contents("php://input"), true);


if (!isset($data['amount'], $data['user_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Amount and user ID are required"]);
    exit;
}

$amount = $data['amount'];
$user_id = 10;  // For testing purposes, replace with actual user ID from authentication


try {
    // Create a payment intent
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $amount * 100, // Amount in cents
        'currency' => 'usd',
        'payment_method_types' => ['card', 'affirm', 'afterpay_clearpay'],
        'description' => 'Payment for order',
    ]);

    // Save payment details to the database
    global $conn;
    $stmt = $conn->prepare("INSERT INTO payment_details (user_id, payment_intent_id, amount, currency, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "isdss",
        $user_id,
        $paymentIntent->id,
        $amount,
        $paymentIntent->currency,
        $paymentIntent->status
    );

    if ($stmt->execute()) {
        echo json_encode(['clientSecret' => $paymentIntent->client_secret, 'message' => 'Payment intent created and saved successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save payment details']);
    }

    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>