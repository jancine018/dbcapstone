<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

include 'db.php'; // Include your database connection file

$response = array();

// Check if category is provided
if (!isset($_GET['category']) || empty($_GET['category'])) {
    $response['success'] = false;
    $response['message'] = "Category is required.";
    echo json_encode($response);
    exit();
}

$category = $_GET['category'];

try {
    // Prepare SQL Query
    $stmt = $con->prepare("SELECT product_id, name, description, base_price, brand, image_url FROM products WHERE type = ? AND is_active = 1");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if products exist
    if ($result->num_rows > 0) {
        $products = array();
        while ($row = $result->fetch_assoc()) {
            $products[] = array(
                "id" => $row["product_id"],
                "name" => $row["name"],
                "description" => $row["description"],
                "price" => $row["base_price"],
                "brand" => $row["brand"],
                "image" => $row["image_url"]
            );
        }
        $response['success'] = true;
        $response['data'] = $products;
    } else {
        $response['success'] = false;
        $response['message'] = "No products found for this category.";
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = "Database error: " . $e->getMessage();
}

echo json_encode($response);
?>
