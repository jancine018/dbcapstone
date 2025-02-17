<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require('db.php');

// Check connection
if ($con->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $con->connect_error]));
}

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // ✅ Fetch repair requests for a user
        if (isset($_GET['user_id'])) {
            $user_id = intval($_GET['user_id']);
            $sql = "SELECT * FROM repair_requests WHERE user_id = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $requests = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode($requests);
        } else {
            echo json_encode(["error" => "User ID is required"]);
        }
        break;

    case 'POST':
        // ✅ Create a new repair request (Supports File Upload)
        if (isset($_POST["user_id"], $_POST["product_name"], $_POST["error_description"], $_POST["delivery_option"])) {
            $user_id = intval($_POST["user_id"]);
            $product_name = $_POST["product_name"];
            $error_description = $_POST["error_description"];
            $delivery_option = $_POST["delivery_option"];
            $status = isset($_POST["status"]) ? $_POST["status"] : "Pending";

            // ✅ Handle File Uploads
            $uploaded_files = [];
            if (!empty($_FILES['media']['name'][0])) {
                $upload_dir = "uploads/";
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                foreach ($_FILES['media']['name'] as $key => $name) {
                    $tmp_name = $_FILES['media']['tmp_name'][$key];
                    $file_name = time() . "_" . basename($name); // Prevent duplicates
                    $target_file = $upload_dir . $file_name;

                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $uploaded_files[] = $file_name; // Store only the filename
                    }
                }
            }

            // Convert filenames array to a comma-separated string for database storage
            $media_filenames = !empty($uploaded_files) ? implode(", ", $uploaded_files) : null;

            // ✅ Insert into Database
            $sql = "INSERT INTO repair_requests (user_id, product_name, error_description, media, delivery_option, status) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("isssss", $user_id, $product_name, $error_description, $media_filenames, $delivery_option, $status);

            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Repair request submitted successfully"]);
            } else {
                echo json_encode(["error" => "Database error: " . $stmt->error]);
            }
        } else {
            echo json_encode(["error" => "Missing required fields"]);
        }
        break;

    case 'PATCH':
        // ✅ Update repair request status
        parse_str(file_get_contents("php://input"), $patchData);
        if (isset($_GET['request_id']) && isset($patchData['status'])) {
            $request_id = intval($_GET['request_id']);
            $status = $patchData['status'];

            if (!in_array($status, ["Pending", "Approved", "Declined"])) {
                echo json_encode(["error" => "Invalid status"]);
                exit();
            }

            $sql = "UPDATE repair_requests SET status = ?, updated_at = NOW() WHERE request_id = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("si", $status, $request_id);

            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Repair request status updated"]);
            } else {
                echo json_encode(["error" => "Database error: " . $stmt->error]);
            }
        } else {
            echo json_encode(["error" => "Request ID and status are required"]);
        }
        break;

    case 'DELETE':
        // ✅ Delete a repair request
        if (isset($_GET['request_id'])) {
            $request_id = intval($_GET['request_id']);

            $sql = "DELETE FROM repair_requests WHERE request_id = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("i", $request_id);

            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Repair request deleted"]);
            } else {
                echo json_encode(["error" => "Database error: " . $stmt->error]);
            }
        } else {
            echo json_encode(["error" => "Request ID is required"]);
        }
        break;

    default:
        echo json_encode(["error" => "Invalid request method"]);
        break;
}

$con->close();
?>
