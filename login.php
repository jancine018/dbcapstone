<?php
// Include the database configuration file
require 'db.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the input from the request body
    $data = json_decode(file_get_contents('php://input'), true);

    // Assign variables to incoming data
    $email = $data['email'] ?? null;
    $password = $data['password'] ?? null;

    // Validate required fields
    if (!$email || !$password) {
        // Set a 400 Bad Request status code and return an error message
        http_response_code(400);
        echo json_encode(['error' => 'Email and password are required.']);
        exit();
    }

    // Prepare a select statement to fetch user by email and role
    $sql = "SELECT * FROM users WHERE email = ? AND role = 'Customer'";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 's', $email);

    // Execute the query
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    // Check if the user exists and the password is correct
    if ($user && password_verify($password, $user['password'])) {
        // Start a session for the user
        session_start();
        $_SESSION['user_id'] = $user['user_id'];

        // Set a 200 OK status code and respond with a success message
        http_response_code(200);
        echo json_encode([
            'success' => 'Login successful!',
            'user' => [
                'id' => $user['user_id'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ]);
    } else {
        // Set a 401 Unauthorized status code if credentials are invalid or the user is not a customer
        http_response_code(401);
        echo json_encode(['error' => 'Invalid email or password, or user is not a customer.']);
    }

    // Close the statement and connection
    mysqli_stmt_close($stmt);
    mysqli_close($con);
}
?>
