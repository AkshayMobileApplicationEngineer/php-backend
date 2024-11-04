<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["message" => "Method not allowed"]);
    exit();
}

$query = "SELECT * FROM `products`";
$params = [];

if (isset($_GET['id'])) {
    $id = $_GET['id'];
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
