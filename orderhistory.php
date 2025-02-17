<?php
include 'db.php';

$user_id = $_GET['user_id'];
$query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_date DESC";
$result = mysqli_query($con, $query);

$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $order_id = $row['order_id'];
    
    $items_query = "SELECT * FROM order_items WHERE order_id = $order_id";
    $items_result = mysqli_query($con, $items_query);
    $items = [];

    while ($item_row = mysqli_fetch_assoc($items_result)) {
        $items[] = $item_row;
    }

    $row['items'] = $items;
    $orders[] = $row;
}

echo json_encode(["success" => true, "orders" => $orders]);
?>
