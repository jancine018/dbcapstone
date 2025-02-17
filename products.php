<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Allow cross-origin requests

require 'db.php'; // Include DB connection

// Check if the connection was successful
if (!$con) {
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

// Get the product_id from the query parameters
$productId = isset($_GET['product_id']) && is_numeric($_GET['product_id']) ? (int)$_GET['product_id'] : null;

if ($productId !== null) {
    error_log("Received product_id: $productId"); // Logs the received product_id
} else {
    error_log("No product_id provided in the request."); // Logs if no product_id is provided
}

// Base Query
$query = "
    SELECT 
        p.product_id, 
        p.name AS product_name, 
        p.description, 
        p.base_price, 
        p.type, 
        p.image_url AS product_image,
        v.variant_id, 
        v.variant_name, 
        v.additional_price, 
        v.stock_quantity, 
        v.image_url AS variant_image
    FROM 
        Products p
    LEFT JOIN 
        Variants v ON p.product_id = v.product_id AND v.is_active = 1
    WHERE 
        p.is_active = 1
";

// Apply product_id filter if provided
if ($productId !== null) {
    $query .= " AND p.product_id = $productId"; // Corrected WHERE clause
    error_log("Query filtered by product_id: $productId"); // Logs the filtered query
}

// Sort results
$query .= " ORDER BY p.product_id, v.variant_id;";

$result = mysqli_query($con, $query);

if (!$result) {
    error_log("Query failed: " . mysqli_error($con)); // Logs query failure
    echo json_encode(["error" => "Query failed: " . mysqli_error($con)]);
    exit();
}

error_log("Query executed successfully."); // Logs successful execution

// Process results
$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $id = $row['product_id'];

    // Initialize product if not already set
    if (!isset($products[$id])) {
        $products[$id] = [
            "product_id" => $row['product_id'],
            "name" => $row['product_name'],
            "description" => $row['description'],
            "base_price" => (float) $row['base_price'], // Convert to float
            "type" => $row['type'],
            "image_url" => $row['product_image'],
            "variants" => []
        ];
        error_log("Initialized product with ID: $id"); // Logs initialized product
    }

    // Add variant if it exists
    if (!empty($row['variant_id'])) {
        $products[$id]["variants"][] = [
            "variant_id" => $row['variant_id'],
            "variant_name" => $row['variant_name'],
            "additional_price" => (float) $row['additional_price'], // Convert to float
            "stock_quantity" => (int) $row['stock_quantity'], // Convert to int
            "image_url" => $row['variant_image']
        ];
        error_log("Added variant ID: " . $row['variant_id'] . " to product ID: $id"); // Logs added variant
    }
}

// Return JSON response
if (empty($products)) {
    echo json_encode(["error" => "No product found."]);
} else {
    echo json_encode(array_values($products), JSON_PRETTY_PRINT);
}

// Close database connection
mysqli_close($con);
error_log("Database connection closed.");
?>
