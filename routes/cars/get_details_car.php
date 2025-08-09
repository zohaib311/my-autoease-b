<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS");
header("Content-Type: application/json");

include "../../config_db.php";


$id = $_GET['id'];
$result = $conn->query("SELECT * FROM cars WHERE id = $id");

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(["error" => "Car not found"]);
}
?>
