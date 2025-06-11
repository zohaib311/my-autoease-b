<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require '../../config_db.php';
require '../../controller/auth/validate_token.php';
require '../../vendor/autoload.php'; // Ensure you have the Stripe PHP library installed


\Stripe\Stripe::setApiKey('sk_test_51RJgLmQpF59V7kiLqlA1zyl2Loj2cxnSDZmnTcixZYzCDLO8TZzJg5vF3KpmQvG4QB67zzb3bjE0XcC6UVBgS2eI008RIlUpfj'); // Replace with your Stripe Secret Key

try {
    $user = validateToken(); // Validate user token
    $user_id = $user['id']; // Get user ID from token
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['amount'], $data['installment_id'], $data['order_id'])) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Invalid request data"]);
        exit;
    }

    $amount = $data['amount'];
    $installment_id = $data['installment_id'];
    $order_id = $data['order_id'];

    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'pkr',
                'product_data' => [
                    'name' => "Installment Payment for Order #$order_id",
                ],
                'unit_amount' => $amount * 100, // Stripe expects the amount in cents
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => 'http://localhost:3000/installments?success=true',
        'cancel_url' => 'http://localhost:3000/installments?canceled=true',
    ]);

    echo json_encode(["success" => true, "sessionId" => $session->id]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "An error occurred: " . $e->getMessage()]);
}