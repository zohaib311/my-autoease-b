<?php

// Enable error reporting (TEMPORARY – for testing)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Load Composer
require __DIR__ . '/vendor/autoload.php';

// Load DB config
require __DIR__ . '/config_db.php';

// Load headers
require __DIR__ . '/headers/headers.php';

// Load routes
require  '/routes/admin/orders/get_orders_for_admin.php';
require  '/routes/admin/orders/update_order_status.php';
require  '/routes/admin/reports/get_installments.php';
require  '/routes/admin/reports/get_inventory.php';
require  '/routes/admin/reports/get_orders.php';
require  '/routes/cars/cars.php';
require  '/routes/cars/filter_cars.php';
require  '/routes/cars/get_details_car.php';
require  '/routes/customer/cart/add_to_cart.php';
require  '/routes/customer/cart/get_cart_items.php';
require  '/routes/customer/cart/remove_from_cart.php';
require  '/routes/customer/orders/get_orders_list.php';
require  '/routes/customer/orders/place_order.php';
require  '/routes/customer/orders/update_order_status_user.php';
require  '/routes/installments/get_installments.php';
require  '/routes/installments/save_installments_user.php';
require  '/routes/installments/update_installment_status.php';
require  '/routes/payment-intents/create_checkout_session.php';
require  '/routes/payment-intents/get_payment_details.php';
require  '/routes/payment-intents/save_payment_intent.php';
