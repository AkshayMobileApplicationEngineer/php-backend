<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        "status" => "failed",
      "message" => " {$_SERVER['REQUEST_METHOD']} Method not allowed"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id) || !is_numeric($data->id)) {
    http_response_code(400); // Bad Request
    echo json_encode(["message" => "Invalid or missing ID"]);
    exit();
}

$id = $data->id;
$fieldsToUpdate = [];
$params = [];

if (isset($data->product_name)) {
    $fieldsToUpdate[] = "product_name = ?";
    $params[] = $data->product_name;
}
if (isset($data->product_price)) {
    if (!is_numeric($data->product_price) || $data->product_price < 0) {
        http_response_code(400); // Bad Request
        echo json_encode(["message" => "Invalid price"]);
        exit();
    }
    $fieldsToUpdate[] = "product_price = ?";
    $params[] = $data->product_price;
}
if (isset($data->stock)) {
    if (!is_numeric($data->stock) || $data->stock < 0) {
        http_response_code(400); // Bad Request
        echo json_encode(["message" => "Invalid stock"]);
        exit();
    }
    $fieldsToUpdate[] = "stock = ?";
    $params[] = $data->stock;
}
if (isset($data->discount)) {
    if (!is_numeric($data->discount) || $data->discount < 0) {
        http_response_code(400); // Bad Request
        echo json_encode(["message" => "Invalid discount"]);
        exit();
    }
    $fieldsToUpdate[] = "discount = ?";
    $params[] = $data->discount;
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
