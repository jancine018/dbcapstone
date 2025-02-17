<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization");

require 'db.php'; // Include your database connection

// Get the input data from the request
$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (
    !isset($data['recipient_name']) || 
    !isset($data['street_address']) || 
    !isset($data['barangay']) || 
    !isset($data['city']) || 
    !isset($data['postal_code']) || 
    !isset($data['mobile_number']) || 
    !isset($data['user_id'])
) {
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

// Validate postal_code and mobile_number length
if (strlen($data['postal_code']) !== 4) {
    echo json_encode(["error" => "Postal code must be 4 digits"]);
    exit;
}

if (strlen($data['mobile_number']) !== 11) {
    echo json_encode(["error" => "Mobile number must be 11 digits"]);
    exit;
}

// Assign data to variables
$recipient_name = $data['recipient_name'];
$street_address = $data['street_address'];
$barangay = $data['barangay'];
$city = $data['city'];
$postal_code = $data['postal_code'];
$mobile_number = $data['mobile_number'];
$is_default = isset($data['is_default']) ? intval($data['is_default']) : 0;
$user_id = intval($data['user_id']);

// Prepare SQL query to insert the address
$sql = "INSERT INTO shipping_addresses (user_id, recipient_name, street_address, barangay, city, postal_code, mobile_number, is_default, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

// Prepare and bind
$stmt = $con->prepare($sql);

// Ensure we have the correct number of parameters (8 parameters)
$stmt->bind_param("isssssis", $user_id, $recipient_name, $street_address, $barangay, $city, $postal_code, $mobile_number, $is_default);


// Execute the query
if ($stmt->execute()) {
    // Address added successfully
    $inserted_id = $stmt->insert_id; // Store the insert ID after execution
    
    // If this address is set as default, update others to be non-default
    if ($is_default) {
        $update_sql = "UPDATE shipping_addresses SET is_default = 0 WHERE user_id = ? AND address_id != ?";
        $update_stmt = $con->prepare($update_sql);
        $update_stmt->bind_param("ii", $user_id, $inserted_id); // Use $inserted_id
        $update_stmt->execute();
        $update_stmt->close();
    }

    echo json_encode(["success" => true, "message" => "Address added successfully"]);
} else {
    // Error occurred while adding the address
    echo json_encode(["error" => "Failed to add address", "message" => $stmt->error]);
}

$stmt->close();
$con->close();
?>
