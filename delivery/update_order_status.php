<?php
session_start();
include '../connection.php';

// Check if user is logged in
if(!isset($_SESSION['email'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Check if request is POST and has required parameters
if($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['order_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request parameters']);
    exit();
}

// Get parameters
$order_id = intval($_POST['order_id']);

// Get user details
$email = mysqli_real_escape_string($connection, $_SESSION['email']);
$user_query = "SELECT id FROM login WHERE email = '$email'";
$user_result = mysqli_query($connection, $user_query);
$user = mysqli_fetch_assoc($user_result);
$user_id = $user['id'];

// For debugging - we'll just mark it as completed directly in the database
$update_query = "UPDATE food_donations SET request_notes = CONCAT(IFNULL(request_notes, ''), ' | COMPLETED: " . date('Y-m-d H:i:s') . "') WHERE Fid = $order_id AND assigned_to = $user_id";
$result = mysqli_query($connection, $update_query);

if($result && mysqli_affected_rows($connection) > 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Order marked as completed successfully']);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to update order: ' . mysqli_error($connection)]);
}
?> 