<?php
// ajax_view_month_totals.php
// Make sure this file is included after $start_date, $end_date, and $conn are defined.
$totals_sql = "SELECT DISTINCT order_number, job_name FROM inv_actions WHERE DATE(action_date) BETWEEN '$start_date' AND '$end_date'";
$totals_result = $conn->query($totals_sql);

?>
<h3>Inventory Movement Totals for <?= date('F Y', strtotime($start_date)) ?></h3>
<table border="1" cellspacing="0" cellpadding="5">
    <thead>
        <tr>
            <th>Order #</th>
            <th>Job Name</th>
            <th>HI Pallets</th>
            <th>HI Weight</th>
            <th>HO Pallets</th>
            <th>HO Weight</th>
            <th>Inspect Hours</th>
        </tr>
    </thead>
    <tbody>
<?php
while ($job = $totals_result->fetch_assoc()):
    $order = $job['order_number'];
    $name = $job['job_name'];

    $hi_totals = $conn->query("SELECT SUM(quantity) as pallets_in, SUM(weight) as weight_in 
                               FROM inv_actions 
                               WHERE order_number='$order' AND job_name='$name'
                                 AND action_type='Handling In' 
                                 AND action_date BETWEEN '$start_date' AND '$end_date'")
                     ->fetch_assoc();
    $ho_totals = $conn->query("SELECT SUM(quantity) as pallets_out, SUM(weight) as weight_out 
                               FROM inv_actions 
                               WHERE order_number='$order' AND job_name='$name'
                                 AND action_type='Handling Out' 
                                 AND action_date BETWEEN '$start_date' AND '$end_date'")
                     ->fetch_assoc();
    $inspect_totals = $conn->query("SELECT SUM(hours) as total_hours 
                                    FROM inv_actions 
                                    WHERE order_number='$order' AND job_name='$name'
                                      AND action_type='Open Inspect' 
                                      AND action_date BETWEEN '$start_date' AND '$end_date'")
                         ->fetch_assoc();

    $pallets_in = $hi_totals['pallets_in'] ?? 0;
    $weight_in = $hi_totals['weight_in'] ?? 0;
    $pallets_out = $ho_totals['pallets_out'] ?? 0;
    $weight_out = $ho_totals['weight_out'] ?? 0;
    $inspect_hours = $inspect_totals['total_hours'] ?? 0;
?>
    <tr>
        <td><?=htmlspecialchars($order)?></td>
        <td><?=htmlspecialchars($name)?></td>
        <td><?=number_format($pallets_in,2)?></td>
        <td><?=number_format($weight_in,2)?></td>
        <td><?=number_format($pallets_out,2)?></td>
        <td><?=number_format($weight_out,2)?></td>
        <td><?=number_format($inspect_hours,2)?></td>
    </tr>
<?php endwhile; ?>
    </tbody>
</table>
