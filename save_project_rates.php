<?php
include 'db_connect.php';

$id = $_POST['id'];
$handling_pallet = $_POST['handling_in_out_pallet'];
$inspection_rate = $_POST['inspection_rate_hr'];
$storage_pallet = $_POST['storage_rate_pallet'];
$handling_weight = $_POST['handling_in_out_weight'];
$storage_weight = $_POST['storage_rate_weight'];

$stmt = $conn->prepare("UPDATE inv_rates SET 
    handling_in_out_pallet=?, 
    inspection_rate_hr=?, 
    storage_rate_pallet=?, 
    handling_in_out_weight=?, 
    storage_rate_weight=? 
    WHERE id=?");

$stmt->bind_param('dddddi', $handling_pallet, $inspection_rate, $storage_pallet, $handling_weight, $storage_weight, $id);

if ($stmt->execute()) {
    echo json_encode(['status'=>'success', 'message'=>'Rates updated successfully']);
} else {
    echo json_encode(['status'=>'error', 'message'=>'Database update failed: '.$stmt->error]);
}

$stmt->close();
$conn->close();
?>
