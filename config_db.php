<?php 
require_once __DIR__ . '/includes/app_env.php';

$databaseUrl = app_first_env(['MYSQL_URL', 'DATABASE_URL']);

$servername = app_first_env(['MYSQLHOST', 'DB_HOST'], 'localhost');
$username = app_first_env(['MYSQLUSER', 'DB_USERNAME', 'DB_USER'], 'root');
$password = app_first_env(['MYSQLPASSWORD', 'DB_PASSWORD', 'DB_PASS'], '');
$dbname = app_first_env(['MYSQLDATABASE', 'DB_DATABASE', 'DB_NAME'], 'autoease');
$port = (int) app_first_env(['MYSQLPORT', 'DB_PORT'], '3306');

if ($databaseUrl) {
  $parts = parse_url($databaseUrl);

  if ($parts !== false) {
    $servername = $parts['host'] ?? $servername;
    $username = isset($parts['user']) ? rawurldecode($parts['user']) : $username;
    $password = isset($parts['pass']) ? rawurldecode($parts['pass']) : $password;
    $dbname = isset($parts['path']) ? ltrim($parts['path'], '/') : $dbname;
    $port = isset($parts['port']) ? (int) $parts['port'] : $port;
  }
}

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

//Check connection
if ($conn->connect_error) {
  http_response_code(500);
  echo json_encode(["error" => "Database connection failed"]);
  exit();
}

// Check if the request is for connection status
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['check_connection'])) {
  echo json_encode(["status" => "Connection successful"]);
  $conn->close();
  exit();
}
?>
