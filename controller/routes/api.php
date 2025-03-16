<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include "../db.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method === "GET") {
    $result = $conn->query("SELECT * FROM cars");
    $cars = [];
    while ($row = $result->fetch_assoc()) {
        $cars[] = $row;
    }
    echo json_encode($cars);
}

if ($method === "POST") {
    $data = json_decode(file_get_contents("php://input"));
    $stmt = $conn->prepare("INSERT INTO cars (name, model, price) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $data->name, $data->model, $data->price);
    if ($stmt->execute()) {
        echo json_encode(["message" => "Car added successfully"]);
    } else {
        echo json_encode(["error" => "Failed to add car"]);
    }
}

if ($method === "DELETE") {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM cars WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(["message" => "Car deleted successfully"]);
    } else {
        echo json_encode(["error" => "Failed to delete car"]);
    }
}
?>

