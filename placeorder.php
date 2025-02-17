<?php
include 'db.php'; // Ensure database connection works

$product_id = 6; // Set product_id to 1

$product_check_query = "SELECT COUNT(*) FROM products WHERE product_id = ?";
$product_check_stmt = $con->prepare($product_check_query);
$product_check_stmt->bind_param("i", $product_id);
$product_check_stmt->execute();
$product_check_stmt->bind_result($product_exists);
$product_check_stmt->fetch();
$product_check_stmt->close();

if ($product_exists == 0) {
    echo json_encode(["success" => false, "message" => "Product with ID {$product_id} does not exist"]);
    exit();
}

header("Content-Type: application/json");

// Debugging: Log incoming JSON request
$data = json_decode(file_get_contents("php://input"), true);
file_put_contents("debug_log.txt", print_r($data, true));

if ($data === null) {
    echo json_encode(["success" => false, "message" => "Invalid JSON format"]);
    exit();
}

// Validate required fields
$missing_fields = [];
if (empty($data['user_id'])) $missing_fields[] = 'user_id';
if (empty($data['payment_method'])) $missing_fields[] = 'payment_method';
if (empty($data['cart_items'])) $missing_fields[] = 'cart_items';
if (empty($data['address_id'])) {
    echo json_encode(["success" => false, "message" => "Missing required field: address_id"]);
    exit();
}
if (count($missing_fields) > 0) {
    echo json_encode(["success" => false, "message" => "Missing required fields", "missing_fields" => $missing_fields]);
    exit();
}

$user_id = $data['user_id'];
$payment_method = $data['payment_method'];
$gcash_ref_number = $data['gcash_ref_number'] ?? null;
$gcash_screenshot = $data['gcash_screenshot'] ?? null;
$cart_items = $data['cart_items'];
$address_id = $data['address_id'];

$total_price = 0;

// Start a transaction to ensure atomicity
$con->begin_transaction();

try {
    // Check if address_id exists
    $address_check_query = "SELECT COUNT(*) FROM shipping_addresses WHERE address_id = ?";
    $address_check_stmt = $con->prepare($address_check_query);
    $address_check_stmt->bind_param("i", $address_id);
    $address_check_stmt->execute();
    $address_check_stmt->bind_result($address_exists);
    $address_check_stmt->fetch();
    $address_check_stmt->close();

    if ($address_exists == 0) {
        echo json_encode(["success" => false, "message" => "Address with ID {$address_id} does not exist"]);
        exit();
    }

    // Insert into product_orders
    $order_query = "INSERT INTO product_orders (user_id, total_price, order_status, order_date, updated_at, payment_method, gcash_ref_number, gcash_screenshot, address_id) 
                    VALUES (?, 0, 'pending', NOW(), NOW(), ?, ?, ?, ?)";
    $stmt = $con->prepare($order_query);
    $stmt->bind_param("issss", $user_id, $payment_method, $gcash_ref_number, $gcash_screenshot, $address_id);

    if (!$stmt->execute()) {
        throw new Exception("Failed to insert into product_orders");
    }

    $order_id = $stmt->insert_id;

    foreach ($cart_items as $item) {
        $quantity = $item['quantity'] ?? 1;
        $total_price += $item['total_price'] ?? 0;

        // Insert each item into product_orders (formerly order_items) with product_id = 1
        $item_query = "INSERT INTO product_orders (order_id, product_id, quantity, total_price)
                       VALUES (?, ?, ?, ?)";
        $item_stmt = $con->prepare($item_query);
        $item_stmt->bind_param("iiid", $order_id, $product_id, $quantity, $total_price);
        if (!$item_stmt->execute()) {
            throw new Exception("Failed to insert item into product_orders");
        }
    }

    // Update total price in product_orders
    $update_order_query = "UPDATE product_orders SET total_price = ? WHERE order_id = ?";
    $update_stmt = $con->prepare($update_order_query);
    $update_stmt->bind_param("di", $total_price, $order_id);
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to update total price in product_orders");
    }

    // Commit the transaction
    $con->commit();

    echo json_encode(["success" => true, "message" => "Order placed successfully!", "order_id" => $order_id]);
} catch (Exception $e) {
    $con->rollback();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    $con->close();
}
?>
