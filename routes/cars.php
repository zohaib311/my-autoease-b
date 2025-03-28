<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS");
header("Content-Type: application/json");

include "../config_db.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "OPTIONS") {
    // Handle preflight requests
    exit(0);
}

//post method to post to the database also called CREATE.

if ($method == "POST") {
    if (isset($_FILES['image']) && isset($_POST['name']) && isset($_POST['year']) && isset($_POST['manufacturer']) && isset($_POST['transmission']) && isset($_POST['fuelType']) && isset($_POST['price'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $year = $conn->real_escape_string($_POST['year']);
        $manufacturer = $conn->real_escape_string($_POST['manufacturer']);
        $transmission = $conn->real_escape_string($_POST['transmission']);
        $fuelType = $conn->real_escape_string($_POST['fuelType']);
        $price = $conn->real_escape_string($_POST['price']);
        
        // Handle file upload
        $image = time().$_FILES['image']['name'];
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $target_file);

        $sql = "INSERT INTO cars (name, year, manufacturer, transmission, fuel_type, price, image, created_at)
                VALUES ('$name', '$year', '$manufacturer', '$transmission', '$fuelType', '$price', '$target_file', NOW())";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(["message" => "Car added successfully"]);
        } else {
            echo json_encode(["error" => "Error: " . $conn->error]);
        }
    } else {
        echo json_encode(["error" => "Invalid input"]);
    }
}


// featch request and get from database also called READ.
if ($method == "GET") {
    $sql = "SELECT * FROM cars ORDER BY created_at DESC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $cars = [];
        while($row = $result->fetch_assoc()) {
            $cars[] = $row;
        }
        echo json_encode($cars);
    } else {
        echo json_encode([]);
    }
}


// delete method from database also called DELETE.
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


// Update method that will update in database also called UPDATE.

if ($method == "PUT") {
    $input = json_decode(file_get_contents("php://input"), true);
    if (isset($input['id']) && isset($input['name']) && isset($input['year']) && isset($input['manufacturer']) && isset($input['transmission']) && isset($input['fuelType']) && isset($input['price'])) {
        $id = intval($input['id']);
        $name = $conn->real_escape_string($input['name']);
        $year = $conn->real_escape_string($input['year']);
        $manufacturer = $conn->real_escape_string($input['manufacturer']);
        $transmission = $conn->real_escape_string($input['transmission']);
        $fuelType = $conn->real_escape_string($input['fuelType']);
        $price = $conn->real_escape_string($input['price']);

        $sql = "UPDATE cars SET name='$name', year='$year', manufacturer='$manufacturer', transmission='$transmission', fuel_type='$fuelType', price='$price' WHERE id=$id";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(["message" => "Car updated successfully"]);
        } else {
            echo json_encode(["error" => "Error: " . $conn->error]);
        }
    } else {
        echo json_encode(["error" => "Invalid input"]);
    }
}

$conn->close();
?>