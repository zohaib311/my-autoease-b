<?php
require_once __DIR__ . '/../../headers/headers.php';

// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Headers: Content-Type");
// header("Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS");
// header("Content-Type: application/json");

include "../../config_db.php";

if (!isset($_GET['id']) || !ctype_digit((string) $_GET['id'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Valid car id is required"]);
    $conn->close();
    exit;
}

$id = (int) $_GET['id'];
$result = $conn->query("SELECT * FROM cars WHERE id = $id");

if ($result === false) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Car details query failed",
        "error" => $conn->error,
    ]);
    $conn->close();
    exit;
}

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(["error" => "Car not found"]);
}
?>
