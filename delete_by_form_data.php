<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["message" => "Method not allowed"]);
    exit();
}

// Parse the DELETE request input to get form-data
parse_str(file_get_contents("php://input"), $_DELETE);

if (!isset($_DELETE['id']) || !is_numeric($_DELETE['id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(["message" => "Invalid or missing ID"]);
    exit();
}

$id = $_DELETE['id'];

$query = "DELETE FROM `products` WHERE id = ?";
$stmt = $conn->prepare($query);

if ($stmt === false) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["message" => "Failed to prepare the SQL statement"]);
    exit();
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(["message" => "Product deleted successfully"]);
    } else {
        echo json_encode(["message" => "No product found with the given ID"]);
    }
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(["message" => "Failed to execute the SQL statement"]);
}

$stmt->close();
$conn->close();
?>
