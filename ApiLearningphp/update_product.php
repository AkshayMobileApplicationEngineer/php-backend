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

// Fetch existing product details
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

// Set updated values or default to existing values
$product_name = !empty($data->product_name) ? $data->product_name : $product['product_name'];
$product_price = isset($data->product_price) && is_numeric($data->product_price) ? $data->product_price : $product['product_price'];
$stock = isset($data->stock) && is_numeric($data->stock) ? $data->stock : $product['stock'];
$discount = isset($data->discount) && is_numeric($data->discount) ? $data->discount : $product['discount'];

// Update product details
$update_query = $conn->prepare("UPDATE products SET product_name = ?, product_price = ?, stock = ?, discount = ? WHERE id = ?");
$update_query->bind_param("sdiii", $product_name, $product_price, $stock, $discount, $data->id);

if ($update_query->execute()) {
    http_response_code(200); // OK
    echo json_encode([
        'status' => 'success',
        'message' => 'Product updated successfully'
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
