<?php
// ajax_yearly_totals_data.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connect.php';

// Get the requested year or default to current year
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Identify the system's current date
$nowYear  = date('Y');
$nowMonth = date('n'); // 1..12

echo "<h3>Yearly Totals for $selected_year</h3>";
echo "<table border='1' cellpadding='5' cellspacing='0' style='width:100%; border-collapse:collapse;'>";
echo "<thead><tr>
        <th>Month</th>
        <th>Total Revenue</th>
      </tr></thead><tbody>";

$grand_total_year = 0.0;

// Loop from January..December
for ($m = 1; $m <= 12; $m++) {
    // Build date boundaries for aggregator
    $start_date = sprintf('%04d-%02d-01', $selected_year, $m);
    $end_date   = date('Y-m-t', strtotime($start_date));

    $monthName  = date('F', mktime(0,0,0,$m,1));

    // Decide if we do a full aggregator, partial aggregator with label, or skip (blank).
    // 1) If the selected year is in the past (< nowYear), do full aggregator for all 12 months
    // 2) If the selected year is in the future (> nowYear), show blank for all 12 months
    // 3) If the selected year == nowYear:
    //    - if $m < $nowMonth => full aggregator
    //    - if $m == $nowMonth => aggregator but labeled “(Projected)”
    //    - if $m > $nowMonth => blank row
    $month_total = 0.0;
    $showProjected = false; // we'll set true if it's the current month

    if ($selected_year < $nowYear) {
        // All months are in the past => full aggregator
        $month_total = monthlyAggregator($conn, $start_date, $end_date);
    } elseif ($selected_year > $nowYear) {
        // All months in a future year => no aggregator
        $month_total = 0.0; 
    } else {
        // $selected_year == $nowYear
        if ($m < $nowMonth) {
            // Past month in the current year => aggregator
            $month_total = monthlyAggregator($conn, $start_date, $end_date);
        } elseif ($m == $nowMonth) {
            // Current month => aggregator + label “(Projected)”
            $month_total = monthlyAggregator($conn, $start_date, $end_date);
            $showProjected = true;
        } else {
            // $m > $nowMonth => future month => blank
            $month_total = 0.0; 
        }
    }

    // Add to the year's grand total
    $grand_total_year += $month_total;

    // Display row
    if ($month_total > 0) {
        // e.g. “3,455.00 (Projected)” if $showProjected is true
        $displayVal = "$".number_format($month_total,2);
        if ($showProjected) {
            $displayVal .= " (Projected)";
        }
        echo "<tr>
                <td>$monthName</td>
                <td>$displayVal</td>
              </tr>";
    } else {
        // month_total is 0 => either in the future or no data
        // You can decide to show “0.00”, blank, or “N/A”
        if ($selected_year > $nowYear || ($selected_year==$nowYear && $m>$nowMonth)) {
            // Future => blank
            echo "<tr>
                    <td>$monthName</td>
                    <td> </td>
                  </tr>";
        } else {
            // Past but aggregator = 0 => show $0.00
            echo "<tr>
                    <td>$monthName</td>
                    <td>$".number_format(0,2)."</td>
                  </tr>";
        }
    }
}

// End table body, show a final row
echo "</tbody>
      <tfoot>
         <tr>
           <td style='text-align:right;'><strong>Year $selected_year Total:</strong></td>
           <td><strong>$".number_format($grand_total_year,2)."</strong></td>
         </tr>
      </tfoot>
      </table>";

$conn->close();

// --------------------------------------------------------------------------
// monthlyAggregator() – a helper function to compute the monthly revenue 
// for leftover + in-month approach. This is a minimal version for demonstration.
// --------------------------------------------------------------------------
function monthlyAggregator($conn, $start_date, $end_date) {
    // day before for leftover logic
    $day_before = date('Y-m-d', strtotime("$start_date -1 day"));

    // subqueries for leftover + in-month
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

    $jobs_sql = "($leftover_subq) UNION ($inmonth_subq)";
    $jobs_res = $conn->query($jobs_sql);

    $month_total = 0.0;

    while ($jrow = $jobs_res->fetch_assoc()) {
        $order = $jrow['order_number'];
        $name  = $jrow['job_name'];

        // Get rates
        $rates_sql = "SELECT * FROM inv_rates WHERE order_number='$order' AND job_name='$name'";
        $r = $conn->query($rates_sql)->fetch_assoc();
        if (!$r) {
            $r = [
                'handling_in_out_pallet' => 0,
                'handling_in_out_weight' => 0,
                'storage_rate_pallet'    => 0,
                'storage_rate_weight'    => 0,
                'inspection_rate_hr'     => 0
            ];
        }
        $rp_inpal   = $r['handling_in_out_pallet'];
        $rp_inwt    = $r['handling_in_out_weight'];
        $rp_stpal   = $r['storage_rate_pallet'];
        $rp_stwt    = $r['storage_rate_weight'];
        $rp_insp    = $r['inspection_rate_hr'];

        // Handling In for this month
        $m_in = $conn->query("
          SELECT SUM(quantity) as pal_in, SUM(weight) as wt_in
          FROM inv_actions
          WHERE order_number='$order' AND job_name='$name'
            AND action_type='Handling In'
            AND DATE(action_date) BETWEEN '$start_date' AND '$end_date'
        ")->fetch_assoc();
        $pin_m = $m_in['pal_in'] ?? 0;
        $win_m = $m_in['wt_in']  ?? 0;
        $cost_in = $pin_m*$rp_inpal + $win_m*$rp_inwt;

        // Handling Out this month
        $m_out = $conn->query("
          SELECT SUM(quantity) as pal_out, SUM(weight) as wt_out
          FROM inv_actions
          WHERE order_number='$order' AND job_name='$name'
            AND action_type='Handling Out'
            AND DATE(action_date) BETWEEN '$start_date' AND '$end_date'
        ")->fetch_assoc();
        $pout_m = $m_out['pal_out'] ?? 0;
        $wout_m = $m_out['wt_out']  ?? 0;
        $cost_out = $pout_m*$rp_inpal + $wout_m*$rp_inwt;

        // leftover at end_of_month
        $m_in_end = $conn->query("
          SELECT SUM(quantity) as tot_in, SUM(weight) as wt_in
          FROM inv_actions
          WHERE order_number='$order' AND job_name='$name'
            AND action_type='Handling In'
            AND action_date <= '$end_date'
        ")->fetch_assoc();
        $pin_end = $m_in_end['tot_in'] ?? 0;
        $win_end = $m_in_end['wt_in']  ?? 0;

        $m_out_end = $conn->query("
          SELECT SUM(quantity) as tot_out, SUM(weight) as wt_out
          FROM inv_actions
          WHERE order_number='$order' AND job_name='$name'
            AND action_type='Handling Out'
            AND action_date <= '$end_date'
        ")->fetch_assoc();
        $pout_end = $m_out_end['tot_out'] ?? 0;
        $wout_end = $m_out_end['wt_out'] ?? 0;

        $pin_left  = max($pin_end - $pout_end, 0);
        $win_left  = max($win_end - $wout_end, 0);
        $cost_store= $pin_left*$rp_stpal + $win_left*$rp_stwt;

        // Open Inspect this month
        $m_insp = $conn->query("
          SELECT SUM(hours) as total_hrs
          FROM inv_actions
          WHERE order_number='$order' AND job_name='$name'
            AND action_type='Open Inspect'
            AND DATE(action_date) BETWEEN '$start_date' AND '$end_date'
        ")->fetch_assoc();
        $hrs_insp = $m_insp['total_hrs'] ?? 0;
        $cost_insp= $hrs_insp*$rp_insp;

        // sum total for that job
        $job_month_total= $cost_in + $cost_out + $cost_store + $cost_insp;
        $month_total   += $job_month_total;
    }

    return $month_total;
}
