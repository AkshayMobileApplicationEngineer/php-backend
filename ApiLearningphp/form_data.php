<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');
error_reporting(0);
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "{$_SERVER['REQUEST_METHOD']} Method not allowed"]);
    exit();
}

$query = "SELECT * FROM `products`";
$params = [];

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    if (!is_numeric($id)) {
        http_response_code(400); // Bad Request
        echo json_encode(["message" => "Invalid ID"]);
        exit();
    }
    $query = "SELECT * FROM `products` WHERE id = ?";
    $params[] = $id;
}

$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param("i", ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $output = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(["message" => "All product", "data" => $output]);
} else {
    echo json_encode(["message" => "No products found"]);
}

$stmt->close();
$conn->close();
?>
