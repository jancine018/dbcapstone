<?php
require 'db.php'; // Ensure this is correctly pointing to your database connection file

header('Content-Type: application/json');

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['user_id']) && is_numeric($_GET['user_id']) && $_GET['user_id'] > 0) {
        $user_id = intval($_GET['user_id']);

        // Fetch cart items for the user with price calculation and active status check
        $sql = "SELECT 
                    c.cart_id, 
                    c.user_id, 
                    c.product_id, 
                    c.variant_id, 
                    c.quantity, 
                    p.name, 
                    v.image_url,
                    p.base_price,
                    v.additional_price,
                    v.variant_name,
                    p.is_active AS product_is_active,  -- Check product availability
                    v.is_active AS variant_is_active  -- Check variant availability
                FROM 
                    cart AS c
                JOIN 
                    products AS p ON c.product_id = p.product_id
                LEFT JOIN 
                    variants AS v ON c.variant_id = v.variant_id
                WHERE 
                    c.user_id = ?";

        $stmt = $con->prepare($sql);
        if (!$stmt) {
            echo json_encode(["success" => false, "message" => "Failed to prepare SQL statement."]);
            exit();
        }

        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $cartItems = [];
        while ($row = $result->fetch_assoc()) {
            // Check if the product or variant is inactive
            $isProductActive = $row['product_is_active'] == 1;
            $isVariantActive = $row['variant_is_active'] == 1;

            // Calculate total price
            $basePrice = $row['base_price'] ?? 0; // Default to 0 if null
            $additionalPrice = $row['additional_price'] ?? 0;
            $quantity = $row['quantity'] ?? 1; // Default to 1 if null
            $totalPrice = ($basePrice + $additionalPrice) * $quantity;

            // Add item to the cartItems array
            $row['total_price'] = $totalPrice; // Add calculated total price
            $row['is_available'] = $isProductActive && $isVariantActive; // Add availability flag

            $cartItems[] = $row; // Append the item
        }

        // Return cart items (including inactive ones with a warning)
        echo json_encode(["success" => true, "data" => $cartItems]);
    } else {
        // Handle case where user_id is not valid
        echo json_encode(["success" => false, "message" => "Valid User ID is required."]);
    }
} else {
    // Return error for unsupported request methods
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}
