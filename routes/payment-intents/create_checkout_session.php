<?php
require_once __DIR__ . '/../../headers/headers.php';
require_once '../../vendor/autoload.php';
require_once '../../config_db.php'; // Include your database connection
require_once '../../controller/auth/validate_token.php'; // Include authentication middleware

$stripeSecretKey = app_env('STRIPE_SECRET_KEY', '');
\Stripe\Stripe::setApiKey($stripeSecretKey);

// header("Access-Control-Allow-Origin: http://localhost:3000");
// header("Access-Control-Allow-Methods: POST, OPTIONS");
// header("Access-Control-Allow-Headers: Content-Type, Authorization");
// header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $user = validateToken(); // This function should decode the token and return user details
    $user_id = $user['id']; // Extract user_id from the decoded token
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized: " . $e->getMessage()]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

// Accept either car_id or installment_id
if (!isset($data['amount'], $data['order_id']) || (!isset($data['car_id']) && !isset($data['installment_id']))) {
    http_response_code(400);
    echo json_encode(["error" => "Amount, order_id and car_id or installment_id are required"]);
    exit;
}

$amount = $data['amount'];
$order_id = $data['order_id'];
$car_id = isset($data['car_id']) ? $data['car_id'] : null;
$installment_id = isset($data['installment_id']) ? $data['installment_id'] : null;

// Use car_id or installment_id for session/product name
$productName = $car_id
    ? "Car Purchase (Order ID: $order_id)"
    : "Installment Payment (Order ID: $order_id, Installment ID: $installment_id)";

try {
    if ($stripeSecretKey === '') {
        throw new Exception("Stripe secret key is not configured.");
    }

    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'pkr',
                'product_data' => [
                    'name' => $productName,
                ],
                'unit_amount' => $amount * 100,
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => app_frontend_url() . '/' . ($car_id ?? 'installment') . '/place-order/order-success?session_id={CHECKOUT_SESSION_ID}&order_id=' . $order_id,
        'cancel_url' => app_frontend_url() . '/' . ($car_id ?? 'installment') . '/place-order/order-cancel',
    ]);

    // Save payment details in the database, including installment_id if present
    $stmt = $conn->prepare("INSERT INTO payment_details (user_id, order_id, amount, currency, status, installment_id) VALUES (?, ?, ?, ?, ?, ?)");
    $currency = 'pkr';
    $status = 'pending';
    $stmt->bind_param("iidssi", $user_id, $order_id, $amount, $currency, $status, $installment_id);

    if ($stmt->execute()) {
        echo json_encode(['id' => $session->id, 'message' => 'Checkout session created successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save payment details']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}


?>
