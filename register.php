<?php
// Include the database configuration file
require 'db.php'; // Ensure this path is correct

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the input from the request body
    $data = json_decode(file_get_contents('php://input'), true);

    // Assign variables to incoming data
    $name = $data['name'] ?? null;
    $email = $data['email'] ?? null;
    $password = $data['password'] ?? null;
    $confirm_password = $data['confirm_password'] ?? null;

    // Validate required fields
    if (!$name || !$email || !$password || !$confirm_password) {
        echo json_encode(['error' => 'All fields are required.']);
        exit();
    }

    // Check if the passwords match
    if ($password !== $confirm_password) {
        echo json_encode(['error' => 'Passwords do not match.']);
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Assign the role as 'Customer'
    $role = 'Customer';

    // Prepare an insert statement with the role
    $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $sql);

    // Bind parameters
    mysqli_stmt_bind_param($stmt, 'ssss', $name, $email, $hashed_password, $role);

    // Execute the query and respond with JSON
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => 'Registration successful!']);
    } else {
        echo json_encode(['error' => 'Could not register user.']);
    }

    // Close statement
    mysqli_stmt_close($stmt);
}

// Close the database connection
mysqli_close($con);
?>
