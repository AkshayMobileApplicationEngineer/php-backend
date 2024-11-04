<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include 'db.php';
error_reporting(0);

$data = json_decode(file_get_contents("php://input"));

if ($data) {
    // Validate input fields
    $errors = [];

    if (empty($data->product_name)) {
        $errors[] = "Product Name is required";
    } else {
        $product_name = $conn->real_escape_string($data->product_name);
    }

    if (empty($data->product_price)) {
        $errors[] = "Product Price is required";
    } elseif (!is_numeric($data->product_price) || $data->product_price < 0) {
        $errors[] = "Product Price must be a non-negative number";
    } else {
        $product_price = $conn->real_escape_string($data->product_price);
    }

    if (empty($data->stock)) {
        $errors[] = "Stock is required";
    } elseif (!is_numeric($data->stock) || $data->stock < 0) {
        $errors[] = "Stock must be a non-negative number";
    } else {
        $stock = $conn->real_escape_string($data->stock);
    }

    if (empty($data->discount)) {
        $errors[] = "Discount is required";
    } elseif (!is_numeric($data->discount) || $data->discount < 0) {
        $errors[] = "Discount must be a non-negative number";
    } else {
        $discount = $conn->real_escape_string($data->discount);
    }

    // If there are errors, return them
    if (!empty($errors)) {
        echo json_encode(
            [
                'status' => "failed",
                'message' => "Validation errors",
                'errors' => $errors
            ]
        );
    } else {
        // Insert the validated data into the database
        $query = "INSERT INTO `products`(`product_name`, `product_price`, `stock`, `discount`) VALUES ('$product_name', '$product_price', '$stock', '$discount')";
        $result = $conn->query($query);

        if ($result) {
            echo json_encode(
                [
                    'status' => "success",
                    'message' => "Product Added Successfully"
                ]
            );
        } else {
            echo json_encode(
                [
                    'status' => "failed",
                    'message' => "Product Not Added"
                ]
            );
        }
    }
} else {
    echo json_encode(
        [
            'status' => "failed",
            'message' => "Invalid Input"
        ]
    );
}
?>
