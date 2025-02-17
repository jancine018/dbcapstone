<?php

// Include your database connection
include 'db.php';

// Function to fetch users or a specific user by ID
function getUsers($id = null)
{
    global $con; // Use the global database connection

    if ($id) {
        // If an ID is provided, fetch that specific user
        $sql_user = "SELECT id, first_name, last_name, username, email, created_at FROM users WHERE id = ?";
        $stmt = $con->prepare($sql_user);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Check if the user was found
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            return json_encode([
                'data' => [
                    'user' => $user
                ]
            ]);
        } else {
            return json_encode([
                'error' => 'User not found'
            ]);
        }
    } else {
        // If no ID is provided, fetch all users
        $sql_users = "SELECT id, first_name, last_name, username, email, created_at FROM users";
        $result_users = $con->query($sql_users);
        
        // Initialize array to hold users
        $users = array();

        // If there are results, fetch them
        if ($result_users->num_rows > 0) {
            while ($row = $result_users->fetch_assoc()) {
                $users[] = $row;
            }
        }

        // Return all users in JSON format
        return json_encode([
            'data' => [
                'users' => $users
            ]
        ]);
    }
}

// Get the ID parameter from the query string (if provided)
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Set the header to return JSON content
header('Content-Type: application/json');

// Call the function and echo the result
echo getUsers($id);

// Close the database connection
$con->close();
?>
