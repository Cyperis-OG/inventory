<?php
include 'db_connect.php';

header('Content-Type: application/json'); // ensure proper response format

$order_number = $_POST['order_number'] ?? '';

if (!$order_number) {
    echo json_encode(['job_name' => '']);
    exit;
}

$stmt = $conn->prepare("SELECT job_name FROM inv_actions WHERE order_number = ? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param('s', $order_number);
$stmt->execute();
$stmt->bind_result($job_name);
$stmt->fetch();

echo json_encode(['job_name' => $job_name ?: '']);

$stmt->close();
$conn->close();
?>
