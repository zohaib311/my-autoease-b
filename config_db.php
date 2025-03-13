<?php 
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "autoease";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

//Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Check if the request is for connection status
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['check_connection'])) {
  echo json_encode(["status" => "Connection successful"]);
  $conn->close();
  exit();
}
?>