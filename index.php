<?php
require_once __DIR__ . '/includes/app_env.php';

app_send_cors_headers();

$baseDir = realpath(__DIR__);
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = '/' . trim(rawurldecode($path), '/');
$path = $path === '/' ? '/' : rtrim($path, '/');
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

$routes = [
    '/auth/login' => 'controller/auth/login.php',
    '/auth/register' => 'controller/auth/register.php',
    '/auth/validate-token' => 'controller/auth/validate_token.php',
    '/users/delete' => 'controller/users/delete_user.php',
    '/cars' => 'routes/cars/cars.php',
    '/cars/filter' => 'routes/cars/filter_cars.php',
    '/cars/details' => 'routes/cars/get_details_car.php',
    '/installments' => 'routes/installments/get_installments.php',
    '/installments/save' => 'routes/installments/save_installments_user.php',
    '/installments/status' => 'routes/installments/update_installment_status.php',
    '/payment-intents/checkout-session' => 'routes/payment-intents/create_checkout_session.php',
    '/payment-intents/details' => 'routes/payment-intents/get_payment_details.php',
    '/payment-intents/save' => 'routes/payment-intents/save_payment_intent.php',
    '/customer/orders' => 'routes/customer/orders/get_orders_list.php',
    '/customer/orders/place' => 'routes/customer/orders/place_order.php',
    '/customer/orders/status' => 'routes/customer/orders/update_order_status_user.php',
    '/customer/cart/add' => 'routes/customer/cart/add_to_cart.php',
    '/customer/cart/items' => 'routes/customer/cart/get_cart_items.php',
    '/customer/cart/remove' => 'routes/customer/cart/remove_from_cart.php',
    '/admin/orders' => 'routes/admin/orders/get_orders_for_admin.php',
    '/admin/orders/status' => 'routes/admin/orders/update_order_status.php',
    '/admin/reports/installments' => 'routes/admin/reports/get_installments.php',
    '/admin/reports/inventory' => 'routes/admin/reports/get_inventory.php',
    '/admin/reports/orders' => 'routes/admin/reports/get_orders.php',
];

$methodRoutes = [
    'GET /auth/profile' => 'controller/auth/get_user_profile.php',
    'POST /auth/profile' => 'controller/auth/save_user_profile.php',
    'GET /users' => 'controller/users/get_users.php',
];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($path === '/') {
    echo json_encode([
        'name' => 'AutoEase API',
        'status' => 'ok',
        'routes' => array_keys($routes),
        'method_routes' => array_keys($methodRoutes),
    ]);
    exit;
}

$target = $methodRoutes[$method . ' ' . $path] ?? $routes[$path] ?? null;

if ($target === null && preg_match('#^/(routes|controller)/.+\.php$#', $path)) {
    $target = ltrim($path, '/');
}

if ($target === null) {
    http_response_code(404);
    echo json_encode(['error' => 'Route not found']);
    exit;
}

dispatch_route($baseDir, $target);

function dispatch_route(string $baseDir, string $relativeFile): void
{
    $file = realpath($baseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativeFile));

    if ($file === false || !str_starts_with($file, $baseDir) || pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
        exit;
    }

    $previousDirectory = getcwd();
    chdir(dirname($file));
    require $file;

    if ($previousDirectory !== false) {
        chdir($previousDirectory);
    }
}

