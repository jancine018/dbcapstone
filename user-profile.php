<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database config
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json'); // Ensure the response is JSON

    $user_id = $_GET['user_id'] ?? null;

    if (!$user_id) {
        http_response_code(400);
        echo json_encode(['error' => 'User ID is required.'], JSON_PRETTY_PRINT);
        exit();
    }

    // Test database connection
    if (!$con) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed.'], JSON_PRETTY_PRINT);
        exit();
    }

    $sql = "SELECT user_id, name, email, role, created_at FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        http_response_code(200);
        echo json_encode(['user' => $user], JSON_PRETTY_PRINT);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'User not found.'], JSON_PRETTY_PRINT);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($con);
}
?>
