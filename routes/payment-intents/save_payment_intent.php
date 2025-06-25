<?php
require_once '../../vendor/autoload.php';
require_once '../../config_db.php';
require_once '../../controller/auth/validate_token.php';

\Stripe\Stripe::setApiKey('sk_test_51RJgLmQpF59V7kiLqlA1zyl2Loj2cxnSDZmnTcixZYzCDLO8TZzJg5vF3KpmQvG4QB67zzb3bjE0XcC6UVBgS2eI008RIlUpfj');

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $user = validateToken();
    $user_id = $user['id'];
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized: " . $e->getMessage()]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['session_id']) || !isset($data['order_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "session_id and order_id are required"]);
    exit;
}

$session_id = $data['session_id'];
$order_id = $data['order_id'];

try {
    // Retrieve the Stripe session and payment intent
    $session = \Stripe\Checkout\Session::retrieve($session_id);
    $paymentIntentId = $session->payment_intent;

    if (!$paymentIntentId) {
        throw new Exception("No payment_intent found for this session.");
    }

    // Optionally, retrieve the payment intent object
    $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

    // Save payment_intent_id to your DB (example for payment_details table)
    $stmt = $conn->prepare("UPDATE payment_details SET payment_intent_id = ?, status = ? WHERE order_id = ? AND user_id = ?");
    $stmt->bind_param("ssii", $paymentIntentId, $paymentIntent->status, $order_id, $user_id);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "payment_intent_id" => $paymentIntentId,
            "status" => $paymentIntent->status
        ]);
    } else {
        throw new Exception("Failed to update payment_intent_id in database.");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>