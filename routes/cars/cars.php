<?php
require_once __DIR__ . '/../../headers/headers.php';

// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Headers: Content-Type");
// header("Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS");
// header("Content-Type: application/json");

include __DIR__ . "/../../config_db.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method == "OPTIONS") {
        http_response_code(200);
    exit(0);
}

//post method to post to the database also called CREATE.

if ($method == "POST") {
    if (isset($_FILES['image']) && isset($_POST['name']) && isset($_POST['year']) && isset($_POST['manufacturer']) && isset($_POST['transmission']) && isset($_POST['fuel_type']) && isset($_POST['price'])  && isset($_POST['available_city'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $year = $conn->real_escape_string($_POST['year']);
        $manufacturer = $conn->real_escape_string($_POST['manufacturer']);
        $transmission = $conn->real_escape_string($_POST['transmission']);
        $fuel_type = $conn->real_escape_string($_POST['fuel_type']);
        $price = $conn->real_escape_string($_POST['price']);
        $available_city = $conn->real_escape_string($_POST['available_city']);
        
        // Handle file upload
        $image = time().$_FILES['image']['name'];
        $target_dir = __DIR__ . "/../../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0775, true);
        }

        $target_file = $target_dir . basename($image);
        $stored_file = "uploads/" . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $target_file);

        $sql = "INSERT INTO cars (name, year, manufacturer, transmission, fuel_type, price, image, created_at, available_city)
                VALUES ('$name', '$year', '$manufacturer', '$transmission', '$fuel_type', '$price', '$stored_file', NOW(), '$available_city')";

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
    if (isset($input['id']) && isset($input['name']) && isset($input['year']) && isset($input['manufacturer']) && isset($input['transmission']) && isset($input['fuel_type']) && isset($input['price'])  && isset($input['available_city'])) {
        $id = intval($input['id']);
        $name = $conn->real_escape_string($input['name']);
        $year = $conn->real_escape_string($input['year']);
        $manufacturer = $conn->real_escape_string($input['manufacturer']);
        $transmission = $conn->real_escape_string($input['transmission']);
        $fuel_type = $conn->real_escape_string($input['fuel_type']);
        $price = $conn->real_escape_string($input['price']);
        $available_city = $conn->real_escape_string($input['available_city']);
        
        $sql = "UPDATE cars SET name='$name', year='$year', manufacturer='$manufacturer', transmission='$transmission', fuel_type='$fuel_type', price='$price', available_city='$available_city' WHERE id=$id";

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
