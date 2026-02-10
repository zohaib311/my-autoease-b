

<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/headers/headers.php';
require __DIR__ . '/includes/cors.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// remove "/api"
$uri = str_replace('/api', '', $uri);

switch ($uri) {
    case '/cars':
        require __DIR__ . '/routes/cars/cars.php';
        break;

    case '/cars/details':
        require __DIR__ . '/routes/cars/get_details_car.php';
        break;

    default:
        http_response_code(404);
        echo json_encode(["error" => "Route not found"]);
}


// // Load routes
// require __DIR__ . '/routes/admin/orders/get_orders_for_admin.php';
// require __DIR__ . '/routes/admin/orders/update_order_status.php';
// require __DIR__ . '/routes/admin/reports/get_installments.php';
// require __DIR__ . '/routes/admin/reports/get_inventory.php';
// require __DIR__ . '/routes/admin/reports/get_orders.php';
// require __DIR__ . '/routes/cars/cars.php';
// require __DIR__ . '/routes/cars/filter_cars.php';
// require __DIR__ . '/routes/cars/get_details_car.php';
// require __DIR__ . '/routes/customer/cart/add_to_cart.php';
// require __DIR__ . '/routes/customer/cart/get_cart_items.php';
// require __DIR__ . '/routes/customer/cart/remove_from_cart.php';
// require __DIR__ . '/routes/customer/orders/get_orders_list.php';
// require __DIR__ . '/routes/customer/orders/place_order.php';
// require __DIR__ . '/routes/customer/orders/update_order_status_user.php';
// require __DIR__ . '/routes/installments/get_installments.php';
// require __DIR__ . '/routes/installments/save_installments_user.php';
// require __DIR__ . '/routes/installments/update_installment_status.php';
// require __DIR__ . '/routes/payment-intents/create_checkout_session.php';
// require __DIR__ . '/routes/payment-intents/get_payment_details.php';
// require __DIR__ . '/routes/payment-intents/save_payment_intent.php';
