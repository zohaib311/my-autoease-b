<?php
require_once __DIR__ . '/includes/app_env.php';

$missingConfig = [];

foreach (['DB_HOST', 'DB_USER', 'DB_NAME'] as $key) {
    if (app_env($key) === null) {
        $missingConfig[] = $key;
    }
}

if ($missingConfig !== [] && ($_SERVER['HTTP_HOST'] ?? '') !== '' && ($_SERVER['HTTP_HOST'] ?? 'localhost') !== 'localhost') {
    http_response_code(500);
    die(json_encode([
        "success" => false,
        "message" => "Database config is missing on the server",
        "missing" => $missingConfig,
        "help" => "Create includes/server_config.php from includes/server_config.example.php or upload a .env file.",
    ]));
}

$servername = app_env('DB_HOST', 'localhost');
$username = app_env('DB_USER', 'root');
$password = app_env('DB_PASS', '');
$dbname = app_env('DB_NAME', 'autoease');
$port = (int) app_env('DB_PORT', '3306');

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode([
        "success" => false,
        "message" => "Database connection failed",
    ]));
}

$conn->set_charset('utf8mb4');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['check_connection'])) {
    header('Content-Type: application/json');
    echo json_encode(["status" => "Connection successful"]);
    $conn->close();
    exit();
}
?>
