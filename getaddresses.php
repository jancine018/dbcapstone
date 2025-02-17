<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization");

require 'db.php'; // Include your database connection

// Get user_id from request
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id <= 0) {
    echo json_encode(["error" => "Invalid user_id"]);
    exit;
}

// Fetch addresses
$sql = "SELECT address_id, user_id, recipient_name, street_address, barangay, city, postal_code, mobile_number, is_default, created_at, updated_at 
        FROM shipping_addresses 
        WHERE user_id = ?";

$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$addresses = [];

while ($row = $result->fetch_assoc()) {
    $addresses[] = $row;
}

$stmt->close();
$con->close();

// Return JSON response
echo json_encode(["addresses" => $addresses]);
?>
