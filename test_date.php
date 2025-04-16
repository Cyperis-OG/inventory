<?php
// test_date.php - This file tests if the DATE() filtering returns rows

error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connect.php';

// Set the date boundaries for testing – change these as needed
$start_date = '2025-03-01';
$end_date   = '2025-03-31';

// Build the query using DATE() to ignore the time component
$query = "SELECT COUNT(*) as total FROM inv_actions WHERE DATE(action_date) BETWEEN '$start_date' AND '$end_date'";

// Execute and fetch the result
$result = $conn->query($query);
if (!$result) {
    echo "Query error: " . $conn->error;
    exit;
}
$data = $result->fetch_assoc();

// Output the total number of records found
echo "<p>Total records between $start_date and $end_date: " . $data['total'] . "</p>";

$conn->close();
?>
