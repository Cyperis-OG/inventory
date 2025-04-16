<?php
// view_month_index_content.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'db_connect.php';

// Fetch distinct months
$month_result = $conn->query("SELECT DISTINCT DATE_FORMAT(action_date, '%Y-%m') as month FROM inv_actions ORDER BY month DESC");
$months = [];
while ($row = $month_result->fetch_assoc()) {
    $months[] = $row['month'];
}
$selected_month = date('Y-m');
$conn->close();
?>
<h2>Monthly Inventory View</h2>
<label for="month">Select Month:</label>
<select id="month" name="month">
    <?php foreach ($months as $month): ?>
        <option value="<?= htmlspecialchars($month) ?>" <?= ($month === $selected_month ? 'selected' : '') ?>>
            <?= date('F Y', strtotime($month . '-01')) ?>
        </option>
    <?php endforeach; ?>
</select>

<!-- The area that will contain the monthly data from ajax_view_month_content.php -->
<div id="monthContentArea" style="margin-top: 20px;"></div>
