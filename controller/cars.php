<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

include '../config_db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "GET") {
    $result = $conn->query("SELECT * FROM cars");
    $cars = [];
    while ($row = $result->fetch_assoc()) {
        $cars[] = $row;
    }
    echo json_encode($cars);
}

if ($method == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!empty($data["name"]) && !empty($data["model"])) {
        $name = $conn->real_escape_string($data["name"]);
        $model = $conn->real_escape_string($data["model"]);
        $manufacturer = $conn->real_escape_string($data["manufacturer"]);
        $transmission = $conn->real_escape_string($data["transmission"]);
        $fuel_type = $conn->real_escape_string($data["fuelType"]);
        $price = $conn->real_escape_string($data["price"]);
        $image = $conn->real_escape_string($data["image"]);

        $sql = "INSERT INTO cars (name, model, manufacturer, transmission, fuel_type, price, image)
                VALUES ('$name', '$model', '$manufacturer', '$transmission', '$fuel_type', '$price', '$image')";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(["message" => "Car added successfully"]);
        } else {
            echo json_encode(["error" => "Error: " . $conn->error]);
        }
    } else {
        echo json_encode(["error" => "Invalid input"]);
    }
}

if ($method == "DELETE") {
    $id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
    if ($id > 0) {
        $sql = "DELETE FROM cars WHERE id = $id";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(["message" => "Car deleted successfully"]);
        } else {
            echo json_encode(["error" => "Error: " . $conn->error]);
        }
    } else {
        echo json_encode(["error" => "Invalid car ID"]);
    }
}

$conn->close();
?>
