<?php
// Include the database connection
require('db.php'); // This should be your actual path to db.php

// Check if the 'cart_id' parameter is provided
if (isset($_GET['cart_id'])) {
    $cart_id = $_GET['cart_id'];

    // Sanitize the input to prevent SQL injection
    $cart_id = filter_var($cart_id, FILTER_SANITIZE_NUMBER_INT);

    // Ensure the cart_id is valid
    if ($cart_id && is_numeric($cart_id)) {
        // Prepare the SQL query to delete the cart item
        $sql = "DELETE FROM cart WHERE cart_id = ?";

        // Prepare and execute the statement
        if ($stmt = $con->prepare($sql)) {
            // Bind the parameters
            $stmt->bind_param('i', $cart_id);

            // Execute the query
            if ($stmt->execute()) {
                // Check if the item was deleted
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['success' => true, 'message' => 'Item deleted successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Item not found']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete item']);
            }

            // Close the statement
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare statement']);
        }

        // Close the database connection
        $con->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid cart ID']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'cart_id parameter is missing']);
}
?>
