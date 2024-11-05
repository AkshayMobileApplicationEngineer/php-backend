<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include 'db.php';
error_reporting(0);

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        'status' => 'failed',
        'message' => "{$_SERVER['REQUEST_METHOD']} Method not allowed"
    ]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id) || !is_numeric($data->id)) {
    http_response_code(400); // Bad Request
    echo json_encode([
        'status' => 'failed',
        'message' => 'Invalid or missing product ID'
    ]);
    exit();
}

// Fetch existing product to ensure it exists
$query = $conn->prepare("SELECT * FROM products WHERE id = ?");
$query->bind_param("i", $data->id);
$query->execute();
$result = $query->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    http_response_code(404); // Not Found
    echo json_encode([
        'status' => 'failed',
        'message' => 'Product not found'
    ]);
    exit();
}

// Build the update query dynamically based on provided fields
$update_fields = [];
$params = [];
$types = "";
$updated_fields = []; // To store names of updated fields

// Update fields based on provided values in the request
if (!empty($data->product_name)) {
    $update_fields[] = "product_name = ?";
    $params[] = $data->product_name;
    $types .= "s";
    $updated_fields[] = 'product name';
}
if (isset($data->product_price) && is_numeric($data->product_price)) {
    $update_fields[] = "product_price = ?";
    $params[] = $data->product_price;
    $types .= "d";
    $updated_fields[] = 'product price';
}
if (isset($data->stock) && is_numeric($data->stock)) {
    $update_fields[] = "stock = ?";
    $params[] = $data->stock;
    $types .= "i";
    $updated_fields[] = 'stock';
}
if (isset($data->discount) && is_numeric($data->discount)) {
    $update_fields[] = "discount = ?";
    $params[] = $data->discount;
    $types .= "i";
    $updated_fields[] = 'discount';
}

if (empty($update_fields)) {
    http_response_code(400); // Bad Request
    echo json_encode([
        'status' => 'failed',
        'message' => 'No valid fields provided for update'
    ]);
    exit();
}

// Add the product ID to parameters and types for WHERE clause
$types .= "i";
$params[] = $data->id;

// Prepare and execute the update query
$query_str = "UPDATE products SET " . implode(", ", $update_fields) . " WHERE id = ?";
$update_query = $conn->prepare($query_str);
$update_query->bind_param($types, ...$params);

if ($update_query->execute()) {
    http_response_code(200); // OK
    echo json_encode([
        'status' => 'success',
        'message' => 'Updated fields: ' . implode(', ', $updated_fields)
    ]);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'status' => 'failed',
        'message' => 'Product not updated due to an error'
    ]);
}

$conn->close();
?>
