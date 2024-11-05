<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include 'db.php';
error_log(0);
error_reporting(0);
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["message" => "{$_SERVER['REQUEST_METHOD']} Method not allowed"]);
    exit();
}

// Parse the PUT request input to get form-data
parse_str(file_get_contents("php://input"), $_POST);

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(["message" => "Invalid or missing ID"]);
    exit();
}

$id = $_POST['id'];
$fieldsToUpdate = [];
$params = [];

if (isset($_POST['product_name'])) {
    $fieldsToUpdate[] = "product_name = ?";
    $params[] = $_POST['product_name'];
}
if (isset($_POST['product_price'])) {
    if (!is_numeric($_POST['product_price']) || $_POST['product_price'] < 0) {
        http_response_code(400); // Bad Request
        echo json_encode(["message" => "Invalid price"]);
        exit();
    }
    $fieldsToUpdate[] = "product_price = ?";
    $params[] = $_POST['product_price'];
}
if (isset($_POST['stock'])) {
    if (!is_numeric($_POST['stock']) || $_POST['stock'] < 0) {
        http_response_code(400); // Bad Request
        echo json_encode(["message" => "Invalid stock"]);
        exit();
    }
    $fieldsToUpdate[] = "stock = ?";
    $params[] = $_POST['stock'];
}
if (isset($_POST['discount'])) {
    if (!is_numeric($_POST['discount']) || $_POST['discount'] < 0) {
        http_response_code(400); // Bad Request
        echo json_encode(["message" => "Invalid discount"]);
        exit();
    }
    $fieldsToUpdate[] = "discount = ?";
    $params[] = $_POST['discount'];
}

if (empty($fieldsToUpdate)) {
    http_response_code(400); // Bad Request
    echo json_encode(["message" => "No fields to update"]);
    exit();
}

$params[] = $id;
$query = "UPDATE `products` SET " . implode(", ", $fieldsToUpdate) . " WHERE id = ?";
$stmt = $conn->prepare($query);

if ($stmt === false) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["message" => "Failed to prepare the SQL statement"]);
    exit();
}

$types = str_repeat('s', count($params) - 1) . 'i';
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(["message" => "Product updated successfully"]);
    } else {
        echo json_encode(["message" => "No product found with the given ID or no changes made"]);
    }
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(["message" => "Failed to execute the SQL statement"]);
}

$stmt->close();
$conn->close();
?>
