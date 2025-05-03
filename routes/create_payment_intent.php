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

$user = validateToken(); // This function should decode the token and return user details

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['amount'], $data['order_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Amount and order ID are required"]);
    exit;
}

$amount = $data['amount'];
$order_id = $data['order_id'];

try {
    // Create a payment intent
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $amount * 100, // Amount in cents
        'currency' => 'pkr',
        'payment_method_types' => ['card'],
        'description' => 'Payment for order',
    ]);

    // Save payment details to the database
    $stmt = $conn->prepare("INSERT INTO payment_details (order_id, user_id, payment_intent_id, amount, currency, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "iisdss",
        $order_id,
        $user_id,
        $paymentIntent->id,
        $amount,
        $paymentIntent->currency,
        $paymentIntent->status
    );

    if ($stmt->execute()) {
        echo json_encode([
            'clientSecret' => $paymentIntent->client_secret,
            'message' => 'Payment intent created and saved successfully',
        ]);
    } else {
        error_log("SQL Error: " . $stmt->error);
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save payment details']);
    }

    $stmt->close();
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>