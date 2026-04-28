<?php
require_once __DIR__ . '/../../headers/headers.php';

// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Headers: Content-Type");
// header("Content-Type: application/json");

include "../../config_db.php";

$manufacturer = isset($_GET['manufacturer']) ? $conn->real_escape_string($_GET['manufacturer']) : '';
$transmission = isset($_GET['transmission']) ? $conn->real_escape_string($_GET['transmission']) : '';
$fuel_type = isset($_GET['fuel_type']) ? $conn->real_escape_string($_GET['fuel_type']) : '';
$available_city = isset($_GET['available_city']) ? $conn->real_escape_string($_GET['available_city']) : '';


// Build the SQL query
$sql = "SELECT * FROM cars WHERE 1=1  ";

if (!empty($manufacturer)) {
    $sql .= " AND manufacturer='$manufacturer'";
}

if (!empty($fuel_type)) {
    $sql .= " AND fuel_type='$fuel_type'";
}
if (!empty($transmission)) {
    $sql .= " AND transmission='$transmission'";
}
if (!empty($available_city)) {
    $sql .= " AND available_city='$available_city'";
}

// Add ORDER BY clause to sort cars in descending order by created_at
$sql .= " ORDER BY created_at DESC";

$result = $conn->query($sql);

if ($result === false) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Cars filter query failed",
        "error" => $conn->error,
    ]);
    $conn->close();
    exit;
}

if ($result->num_rows > 0) {
    $cars = [];
    while ($row = $result->fetch_assoc()) {
        $cars[] = $row;
    }
    echo json_encode($cars);
} else {
    echo json_encode([]); // Return an empty array if no cars are found
}

$conn->close();
?>
