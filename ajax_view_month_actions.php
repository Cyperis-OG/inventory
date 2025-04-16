<?php
// ajax_view_month_actions.php
// This file expects $start_date and $end_date to be set.
$actions_sql = "SELECT * FROM inv_actions WHERE DATE(action_date) BETWEEN '$start_date' AND '$end_date' ORDER BY action_date ASC";
$actions_result = $conn->query($actions_sql);
?>
<h3>Detailed Actions for <?= date('F Y', strtotime($start_date)) ?></h3>
<input type="text" id="search" placeholder="Search Order # or Job Name" onkeyup="filterTable()">
<table id="actionsTable" border="1" cellspacing="0" cellpadding="5">
    <thead>
        <tr>
            <th onclick="sortTable(0)">Date</th>
            <th onclick="sortTable(1)">Time</th>
            <th onclick="sortTable(2)">Action</th>
            <th onclick="sortTable(3)">Order #</th>
            <th onclick="sortTable(4)">Job Name</th>
            <th>Type</th>
            <th>Qty</th>
            <th>Weight (lbs)</th>
            <th>Hours</th>
        </tr>
    </thead>
    <tbody>
<?php
while ($action = $actions_result->fetch_assoc()):
?>
        <tr>
            <td><?=htmlspecialchars($action['action_date'])?></td>
            <td><?=htmlspecialchars($action['action_time'])?></td>
            <td><?=htmlspecialchars($action['action_type'])?></td>
            <td><?=htmlspecialchars($action['order_number'])?></td>
            <td><?=htmlspecialchars($action['job_name'])?></td>
            <td><?=htmlspecialchars($action['type'])?></td>
            <td><?=htmlspecialchars($action['quantity'])?></td>
            <td><?=htmlspecialchars($action['weight'])?></td>
            <td><?=htmlspecialchars($action['hours'])?></td>
        </tr>
<?php endwhile; ?>
    </tbody>
</table>
