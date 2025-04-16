<?php
include 'db_connect.php';

$id = $_POST['id'];
$field = $_POST['field'];
$value = $_POST['value'];

$stmt = $conn->prepare("UPDATE inv_actions SET `$field`=? WHERE id=?");
$stmt->bind_param('si', $value, $id);

if ($stmt->execute()) {
    echo json_encode(['status'=>'success', 'message'=>'Action updated successfully']);
} else {
    echo json_encode(['status'=>'error', 'message'=>'Update failed']);
}
$stmt->close();
$conn->close();
?>
