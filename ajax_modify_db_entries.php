<?php
// ajax_modify_db_entries.php
// Lists all inv_actions rows in descending order of id, 100 per page

include 'db_connect.php';

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;

$limit  = 100;
$offset = ($page - 1) * $limit;

// 1) count total rows
$count_sql = "SELECT COUNT(*) as cnt FROM inv_actions";
$count_res = $conn->query($count_sql);
$count_row = $count_res->fetch_assoc();
$totalRows = $count_row['cnt'];
$totalPages= ceil($totalRows / $limit);

// 2) get the 100 rows for this page
$sql = "SELECT * FROM inv_actions ORDER BY id DESC LIMIT $limit OFFSET $offset";
$res = $conn->query($sql);

?>
<h2>Modify Database Entries (Page <?=$page?>)</h2>
<p>Total Entries: <?=$totalRows?> (showing up to 100 per page)</p>

<table border="1" cellspacing="0" cellpadding="5" style="width:100%;border-collapse:collapse;">
    <thead>
        <tr>
            <th>ID</th>
            <th>Action Type</th>
            <th>Date</th>
            <th>Time</th>
            <th>Order #</th>
            <th>Job Name</th>
            <th>Type</th>
            <th>Qty</th>
            <th>Weight</th>
            <th>Hours</th>
            <th>Save</th>
        </tr>
    </thead>
    <tbody>
<?php
while ($row = $res->fetch_assoc()):
    $id = $row['id'];
    $action_type  = htmlspecialchars($row['action_type']);
    $action_date  = htmlspecialchars($row['action_date']);
    $action_time  = htmlspecialchars($row['action_time']);
    $order_number = htmlspecialchars($row['order_number']);
    $job_name     = htmlspecialchars($row['job_name']);
    $type         = htmlspecialchars($row['type']);
    $quantity     = htmlspecialchars($row['quantity']);
    $weight       = htmlspecialchars($row['weight']);
    $hours        = htmlspecialchars($row['hours']);
?>
    <tr>
        <td><?=$id?></td>
        <td><input type="text" id="row-<?=$id?>-action_type" value="<?=$action_type?>"></td>
        <td><input type="date" id="row-<?=$id?>-action_date" value="<?=$action_date?>"></td>
        <td><input type="time" id="row-<?=$id?>-action_time" value="<?=$action_time?>"></td>
        <td><input type="text" id="row-<?=$id?>-order_number" value="<?=$order_number?>"></td>
        <td><input type="text" id="row-<?=$id?>-job_name" value="<?=$job_name?>"></td>
        <td><input type="text" id="row-<?=$id?>-type" value="<?=$type?>"></td>
        <td><input type="number" id="row-<?=$id?>-quantity" step="1" value="<?=$quantity?>"></td>
        <td><input type="number" id="row-<?=$id?>-weight" step="0.01" value="<?=$weight?>"></td>
        <td><input type="number" id="row-<?=$id?>-hours" step="0.01" value="<?=$hours?>"></td>
        <td>
            <button style="background-color: #007BFF; color: #fff; padding: 5px; border: none; cursor: pointer;" onclick="saveDBEntry(<?=$id?>)">Save</button>
        </td>
    </tr>
<?php endwhile; ?>
    </tbody>
</table>

<!-- Pagination -->
<div style="margin-top:10px;">
<?php
// If there's a previous page
if ($page > 1) {
    $prev = $page - 1;
    echo "<button onclick=\"loadModifyDBEntries($prev)\">Prev</button> ";
}
// If there's a next page
if ($page < $totalPages) {
    $next = $page + 1;
    echo " <button onclick=\"loadModifyDBEntries($next)\">Next</button>";
}
?>
</div>

<?php $conn->close(); ?>
