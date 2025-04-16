<?php
// save_db_entry.php
header('Content-Type: application/json');
include 'db_connect.php';

$id            = $_POST['id'];
$action_type   = $_POST['action_type'];
$action_date   = $_POST['action_date'];
$action_time   = $_POST['action_time'];
$order_number  = $_POST['order_number'];
$job_name      = $_POST['job_name'];
$type          = $_POST['type'];
$quantity      = $_POST['quantity'];
$weight        = $_POST['weight'];
$hours         = $_POST['hours'];

// Validate or sanitize if you want
// e.g., check numeric fields
if (!is_numeric($quantity)) $quantity=0;
if (!is_numeric($weight))   $weight=0;
if (!is_numeric($hours))    $hours=0;

// Build and run the UPDATE
$sql = "UPDATE inv_actions 
           SET action_type='$action_type',
               action_date='$action_date',
               action_time='$action_time',
               order_number='$order_number',
               job_name='$job_name',
               type='$type',
               quantity='$quantity',
               weight='$weight',
               hours='$hours'
         WHERE id='$id'";

if ($conn->query($sql) === TRUE) {
    echo json_encode(['status'=>'success']);
} else {
    echo json_encode(['status'=>'error','message'=>$conn->error]);
}

$conn->close();
?>
