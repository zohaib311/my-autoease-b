<?php
define('APP_ROOT', __DIR__);

require_once APP_ROOT . '/includes/app_env.php';

$routes = [
    '' => null,
    'health' => null,

    'auth/login' => 'controller/auth/login.php',
    'auth/register' => 'controller/auth/register.php',
    'auth/validate-token' => 'controller/auth/validate_token.php',
    'auth/profile' => 'controller/auth/get_user_profile.php',
    'auth/save-profile' => 'controller/auth/save_user_profile.php',

    'users' => 'controller/users/get_users.php',
    'users/delete' => 'controller/users/delete_user.php',

    'cars' => 'routes/cars/cars.php',
    'cars/filter' => 'routes/cars/filter_cars.php',
    'cars/details' => 'routes/cars/get_details_car.php',

    'cart/add' => 'routes/customer/cart/add_to_cart.php',
    'cart/items' => 'routes/customer/cart/get_cart_items.php',
    'cart/remove' => 'routes/customer/cart/remove_from_cart.php',

    'orders/place' => 'routes/customer/orders/place_order.php',
    'orders/list' => 'routes/customer/orders/get_orders_list.php',
    'orders/update' => 'routes/customer/orders/update_order_status_user.php',

    'admin/orders' => 'routes/admin/orders/get_orders_for_admin.php',
    'admin/orders/update' => 'routes/admin/orders/update_order_status.php',
    'admin/reports/orders' => 'routes/admin/reports/get_orders.php',
    'admin/reports/inventory' => 'routes/admin/reports/get_inventory.php',
    'admin/reports/installments' => 'routes/admin/reports/get_installments.php',

    'installments' => 'routes/installments/get_installments.php',
    'installments/save' => 'routes/installments/save_installments_user.php',
    'installments/update' => 'routes/installments/update_installment_status.php',

    'payment-intents/checkout' => 'routes/payment-intents/create_checkout_session.php',
    'payment-intents/save' => 'routes/payment-intents/save_payment_intent.php',
    'payment-intents/details' => 'routes/payment-intents/get_payment_details.php',
];

foreach ($routes as $cleanPath => $filePath) {
    if ($filePath !== null) {
        $routes[$filePath] = $filePath;
    }
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

if ($scriptDir !== '/' && $scriptDir !== '.' && str_starts_with($path, $scriptDir . '/')) {
    $path = substr($path, strlen($scriptDir));
}

$path = trim($path, '/');

if (!array_key_exists($path, $routes)) {
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'API route not found',
        'path' => $path,
    ]);
    exit;
}

if ($routes[$path] === null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'AutoEase API is running',
    ]);
    exit;
}

$target = APP_ROOT . '/' . $routes[$path];

if (!is_file($target)) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Mapped API file is missing',
    ]);
    exit;
}

$previousDirectory = getcwd();
chdir(dirname($target));
$GLOBALS['APP_DISPATCH_TARGET'] = realpath($target);
require $target;
chdir($previousDirectory);
?>
