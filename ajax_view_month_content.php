<?php
// ajax_view_month_content.php
// Show monthly leftover + in-month logic. 
// Use <th onclick="sortTable(index)"> referencing the global sort function in inventory.js.

error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connect.php';

// 1) Determine selected month
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$start_date = $selected_month . '-01';
$end_date   = date('Y-m-t', strtotime($start_date));

// Day before for leftover logic
$day_before = date('Y-m-d', strtotime("$start_date -1 day"));

// Subqueries for leftover & in-month
$leftover_subq = "
  SELECT DISTINCT order_number, job_name
  FROM inv_actions
  GROUP BY order_number, job_name
  HAVING
   (
     COALESCE(
       (SELECT SUM(quantity) 
        FROM inv_actions 
        WHERE order_number = inv_actions.order_number
          AND job_name     = inv_actions.job_name
          AND action_type  = 'Handling In'
          AND action_date <= '$day_before'), 
       0
     )
     -
     COALESCE(
       (SELECT SUM(quantity)
        FROM inv_actions
        WHERE order_number = inv_actions.order_number
          AND job_name     = inv_actions.job_name
          AND action_type  = 'Handling Out'
          AND action_date <= '$day_before'),
       0
     )
   ) > 0
";

$inmonth_subq = "
  SELECT DISTINCT order_number, job_name
  FROM inv_actions
  WHERE DATE(action_date) BETWEEN '$start_date' AND '$end_date'
";

$jobs_sql   = "($leftover_subq) UNION ($inmonth_subq)";
$jobs_result= $conn->query($jobs_sql);
?>
<h2>Monthly Storage & Cost View for <?= htmlspecialchars(date('F Y', strtotime($start_date))) ?></h2>


<!-- Right above the table or in the heading area: -->
<button style="float:right;" onclick="exportMonthPDF()">Export View</button>

<h2>Monthly Storage & Cost View for <?= htmlspecialchars(date('F Y', strtotime($start_date))) ?></h2>



<table border="1" cellspacing="0" cellpadding="5" style="width:100%;border-collapse:collapse;" id="mainRevenueTable">
  <thead>
    <tr>
      <!-- Note the global sortTable(...) calls (no local script needed) -->
      <th onclick="sortTable(0)">Order # <span class="arrowSpan"></span></th>
      <th onclick="sortTable(1)">Job Name <span class="arrowSpan"></span></th>
      <th onclick="sortTable(2)">Start-of-Month Leftover <span class="arrowSpan"></span></th>
      <th onclick="sortTable(3)">In (Month) <span class="arrowSpan"></span></th>
      <th onclick="sortTable(4)">Out (Month) <span class="arrowSpan"></span></th>
      <th onclick="sortTable(5)">End-of-Month Leftover <span class="arrowSpan"></span></th>
      <th onclick="sortTable(6)">Open Inspect <span class="arrowSpan"></span></th>
      <th onclick="sortTable(7)">Total ($) <span class="arrowSpan"></span></th>
    </tr>
  </thead>
  <tbody>
<?php
$grand_total = 0.0;

while ($job = $jobs_result->fetch_assoc()):
    $order = $job['order_number'];
    $name  = $job['job_name'];

    // (A) Rates
    $rate_sql = "SELECT * FROM inv_rates WHERE order_number='$order' AND job_name='$name'";
    $rate_row = $conn->query($rate_sql)->fetch_assoc() ?: [
        'handling_in_out_pallet' => 0,
        'handling_in_out_weight' => 0,
        'storage_rate_pallet'    => 0,
        'storage_rate_weight'    => 0,
        'inspection_rate_hr'     => 0
    ];

    $r_inpal  = $rate_row['handling_in_out_pallet'];
    $r_inwt   = $rate_row['handling_in_out_weight'];
    $r_stpal  = $rate_row['storage_rate_pallet'];
    $r_stwt   = $rate_row['storage_rate_weight'];
    $r_insp   = $rate_row['inspection_rate_hr'];

    // (B) leftover at start-of-month
    $in_b_sql = "
      SELECT SUM(quantity) as qty_in, SUM(weight) as wt_in
      FROM inv_actions 
      WHERE order_number='$order' AND job_name='$name'
        AND action_type='Handling In'
        AND action_date <= '$day_before'
    ";
    $in_b     = $conn->query($in_b_sql)->fetch_assoc() ?: [];
    $pal_in_b = $in_b['qty_in'] ?? 0;
    $wt_in_b  = $in_b['wt_in']  ?? 0;

    $out_b_sql = "
      SELECT SUM(quantity) as qty_out, SUM(weight) as wt_out
      FROM inv_actions 
      WHERE order_number='$order' AND job_name='$name'
        AND action_type='Handling Out'
        AND action_date <= '$day_before'
    ";
    $out_b    = $conn->query($out_b_sql)->fetch_assoc() ?: [];
    $pal_out_b= $out_b['qty_out'] ?? 0;
    $wt_out_b = $out_b['wt_out']  ?? 0;

    $start_pallets = max($pal_in_b - $pal_out_b, 0);
    $start_weight  = max($wt_in_b - $wt_out_b, 0);

    // (C) In/Out this month
    $in_m_sql = "
      SELECT SUM(quantity) as pal_in, SUM(weight) as wt_in
      FROM inv_actions
      WHERE order_number='$order' AND job_name='$name'
        AND action_type='Handling In'
        AND DATE(action_date) BETWEEN '$start_date' AND '$end_date'
    ";
    $in_m = $conn->query($in_m_sql)->fetch_assoc() ?: [];
    $pal_in_m = $in_m['pal_in'] ?? 0;
    $wt_in_m  = $in_m['wt_in']  ?? 0;

    $out_m_sql = "
      SELECT SUM(quantity) as pal_out, SUM(weight) as wt_out
      FROM inv_actions
      WHERE order_number='$order' AND job_name='$name'
        AND action_type='Handling Out'
        AND DATE(action_date) BETWEEN '$start_date' AND '$end_date'
    ";
    $out_m = $conn->query($out_m_sql)->fetch_assoc() ?: [];
    $pal_out_m= $out_m['pal_out'] ?? 0;
    $wt_out_m = $out_m['wt_out']  ?? 0;

    // In/Out cost
    $hi_pallet_cost = $pal_in_m  * $r_inpal;
    $hi_weight_cost = $wt_in_m   * $r_inwt;
    $ho_pallet_cost = $pal_out_m * $r_inpal;
    $ho_weight_cost = $wt_out_m  * $r_inwt;

    // (D) leftover at end-of-month
    $in_end_sql = "
      SELECT SUM(quantity) as tot_in, SUM(weight) as wt_in
      FROM inv_actions
      WHERE order_number='$order' AND job_name='$name'
        AND action_type='Handling In'
        AND action_date <= '$end_date'
    ";
    $in_end = $conn->query($in_end_sql)->fetch_assoc() ?: [];
    $pal_in_end= $in_end['tot_in'] ?? 0;
    $wt_in_end = $in_end['wt_in']  ?? 0;

    $out_end_sql= "
      SELECT SUM(quantity) as tot_out, SUM(weight) as wt_out
      FROM inv_actions
      WHERE order_number='$order' AND job_name='$name'
        AND action_type='Handling Out'
        AND action_date <= '$end_date'
    ";
    $out_end= $conn->query($out_end_sql)->fetch_assoc() ?: [];
    $pal_out_end= $out_end['tot_out'] ?? 0;
    $wt_out_end = $out_end['wt_out']  ?? 0;

    $end_pallets = max($pal_in_end - $pal_out_end, 0);
    $end_weight  = max($wt_in_end   - $wt_out_end, 0);

    $storage_revenue= ($end_pallets*$r_stpal) + ($end_weight*$r_stwt);

    // (E) Open Inspect this month
    $insp_sql= "
      SELECT SUM(hours) as insp_hrs
      FROM inv_actions
      WHERE order_number='$order' AND job_name='$name'
        AND action_type='Open Inspect'
        AND DATE(action_date) BETWEEN '$start_date' AND '$end_date'
    ";
    $insp    = $conn->query($insp_sql)->fetch_assoc() ?: [];
    $insp_hrs= $insp['insp_hrs'] ?? 0;
    $insp_revenue = $insp_hrs*$r_insp;

    // (F) total revenue
    $total_revenue= $hi_pallet_cost + $hi_weight_cost
                    + $ho_pallet_cost + $ho_weight_cost
                    + $storage_revenue + $insp_revenue;

    $grand_total += $total_revenue;

    // (G) Format output
    $start_str = $start_pallets . ' Pal / ' . $start_weight . ' lbs';
    $in_str    = $pal_in_m . ' Pal @ $' . number_format($hi_pallet_cost,2) . '<br>'
                 . $wt_in_m  . ' lbs @ $' . number_format($hi_weight_cost,2);
    $out_str   = $pal_out_m . ' Pal @ $' . number_format($ho_pallet_cost,2) . '<br>'
                 . $wt_out_m . ' lbs @ $' . number_format($ho_weight_cost,2);
    $end_str   = $end_pallets . ' Pal / ' . $end_weight . ' lbs @ $'
                 . number_format($storage_revenue,2);
    $insp_str  = ($insp_hrs>0)
        ? ($insp_hrs . ' hrs @ $' . number_format($insp_revenue,2))
        : '0 hrs @ $0.00';
?>
    <tr>
      <td><?= htmlspecialchars($order) ?></td>
      <td><?= htmlspecialchars($name) ?></td>
      <td><?= $start_str ?></td>
      <td><?= $in_str ?></td>
      <td><?= $out_str ?></td>
      <td><?= $end_str ?></td>
      <td><?= $insp_str ?></td>
      <td><strong><?= number_format($total_revenue,2) ?></strong></td>
    </tr>
<?php endwhile; ?>
  </tbody>
  <tfoot>
    <tr>
      <td colspan="7" style="text-align:right;">
        <strong>Grand Total for <?= htmlspecialchars(date('F Y', strtotime($start_date))) ?>:</strong>
      </td>
      <td><strong><?= number_format($grand_total,2) ?></strong></td>
    </tr>
  </tfoot>
</table>

<?php
// Detailed actions table
$actions_sql = "
  SELECT *
  FROM inv_actions
  WHERE DATE(action_date) BETWEEN '$start_date' AND '$end_date'
  ORDER BY action_date ASC
";
$actions_res= $conn->query($actions_sql);
?>
<h3>Detailed Actions for <?= date('F Y', strtotime($start_date)) ?></h3>
<input type="text" id="search" placeholder="Search Order # or Job Name" onkeyup="filterTable()">

<table id="actionsTable" border="1" cellspacing="0" cellpadding="5" style="width:100%;border-collapse:collapse;">
  <thead>
    <tr>
      <th onclick="sortActions(0)">Date <span class="arrowSpan"></span></th>
      <th onclick="sortActions(1)">Time <span class="arrowSpan"></span></th>
      <th onclick="sortActions(2)">Action <span class="arrowSpan"></span></th>
      <th onclick="sortActions(3)">Order # <span class="arrowSpan"></span></th>
      <th onclick="sortActions(4)">Job Name <span class="arrowSpan"></span></th>
      <th onclick="sortActions(5)">Type <span class="arrowSpan"></span></th>
      <th onclick="sortActions(6)">Qty <span class="arrowSpan"></span></th>
      <th onclick="sortActions(7)">Weight (lbs) <span class="arrowSpan"></span></th>
      <th onclick="sortActions(8)">Hours <span class="arrowSpan"></span></th>
    </tr>
  </thead>
  <tbody>
<?php while ($r = $actions_res->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($r['action_date']) ?></td>
      <td><?= htmlspecialchars($r['action_time']) ?></td>
      <td><?= htmlspecialchars($r['action_type']) ?></td>
      <td><?= htmlspecialchars($r['order_number']) ?></td>
      <td><?= htmlspecialchars($r['job_name']) ?></td>
      <td><?= htmlspecialchars($r['type']) ?></td>
      <td><?= htmlspecialchars($r['quantity']) ?></td>
      <td><?= htmlspecialchars($r['weight']) ?></td>
      <td><?= htmlspecialchars($r['hours']) ?></td>
    </tr>
<?php endwhile; ?>
  </tbody>
</table>

<!-- No local script block needed; all sort code is in inventory.js -->

<?php $conn->close(); ?>
