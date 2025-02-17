<?php
require 'db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate input
    if (isset($data['user_id'], $data['product_id'], $data['variant_id'], $data['quantity'])) {
        $user_id = intval($data['user_id']);
        $product_id = intval($data['product_id']);
        $variant_id = intval($data['variant_id']);
        $quantity = intval($data['quantity']);

        if ($user_id <= 0 || $product_id <= 0 || $variant_id <= 0 || $quantity <= 0) {
            echo json_encode(["success" => false, "message" => "Invalid input data"]);
            exit;
        }

        // Prepare SQL query with ON DUPLICATE KEY UPDATE
        $sql = "INSERT INTO cart (user_id, product_id, variant_id, quantity) 
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE quantity = quantity + ?";
        $stmt = $con->prepare($sql);

        if ($stmt === false) {
            echo json_encode(["success" => false, "message" => "Database error: " . $con->error]);
            exit;
        }

        $stmt->bind_param("iiiii", $user_id, $product_id, $variant_id, $quantity, $quantity);

        // Execute the statement and return response
        if ($stmt->execute()) {
            // Optional: fetch the updated data (like total quantity or price)
            echo json_encode([
                "success" => true,
                "message" => "Item added to cart",
                "product_id" => $product_id,
                "quantity" => $quantity
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to add item to cart: " . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Missing required fields"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$con->close();  // Close the connection (optional if not used elsewhere)
