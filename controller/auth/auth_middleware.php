
<?php
header("Content-Type: application/json");
require_once "../../config_db.php";

function authenticate() {
    $headers = apache_request_headers();
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Authorization token required']);
        exit;
    }

    $token = str_replace('Bearer ', '', $headers['Authorization']);
    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("SELECT * FROM users WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid token']);
        exit;
    }

    $user = $result->fetch_assoc();
    return $user;
}

function isCustomer($user) {
    if ($user['role'] !== 'customer') {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied. Customer role required']);
        exit;
    }
    return true;
}
?>