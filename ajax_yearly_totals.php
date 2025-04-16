<?php
// ajax_yearly_totals.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connect.php';

// 1) Get distinct years from inv_actions
$year_sql = "SELECT DISTINCT YEAR(action_date) as yr FROM inv_actions ORDER BY yr DESC";
$year_res = $conn->query($year_sql);
$years = [];
while ($row = $year_res->fetch_assoc()) {
    $years[] = $row['yr'];
}
$conn->close();
?>
<h2>Monthly & Yearly Totals</h2>
<label for="yearSelect">Select Year:</label>
<select id="yearSelect">
    <?php foreach ($years as $y): ?>
        <option value="<?= htmlspecialchars($y) ?>"><?= htmlspecialchars($y) ?></option>
    <?php endforeach; ?>
</select>

<!-- This is where the aggregator table will appear -->
<div id="yearlyTotalsContainer" style="margin-top:20px;"></div>
