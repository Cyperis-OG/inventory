<?php
include 'db_connect.php';

$type = $_POST['action_type'] ?? null;
$date = $_POST['action_date'] ?? null;
$time = $_POST['action_time'] ?? null;
$order_number = $_POST['order_number'] ?? null;
$job_name = $_POST['job_name'] ?? null;
$item_type = $_POST['type'] ?? null;
$quantity = isset($_POST['quantity']) && $_POST['quantity'] !== '' ? (int)$_POST['quantity'] : null;
$weight = isset($_POST['weight']) && $_POST['weight'] !== '' ? (float)$_POST['weight'] : null;
$hours = isset($_POST['hours']) && $_POST['hours'] !== '' ? (float)$_POST['hours'] : null;

// Basic validation
if (!$type || !$date || !$time || !$order_number || !$job_name) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill out all required fields.']);
    exit;
}

// Prepared statement to prevent SQL injection
$stmt = $conn->prepare("INSERT INTO inv_actions 
(action_type, action_date, action_time, order_number, job_name, type, quantity, weight, hours) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param('ssssssidd', 
    $type, $date, $time, $order_number, $job_name, 
    $item_type, $quantity, $weight, $hours);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Action successfully recorded!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error inserting into database.']);
}

$stmt->close();
$conn->close();
?>
