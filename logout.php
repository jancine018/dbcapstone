<?php
session_start(); // Start the session

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    // Unset all session variables
    $_SESSION = [];

    // Destroy the session
    session_destroy();

    // Respond with a success message
    echo json_encode(['success' => 'Logout successful!']);
} else {
    // If the user is not logged in, respond with an error
    echo json_encode(['error' => 'No user is logged in.']);
}
?>
