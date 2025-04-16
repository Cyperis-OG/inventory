<?php
// view_month_index.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'db_connect.php';

// Get a list of distinct months from the inv_actions table.
$month_result = $conn->query("SELECT DISTINCT DATE_FORMAT(action_date, '%Y-%m') as month FROM inv_actions ORDER BY month DESC");
$months = [];
while ($row = $month_result->fetch_assoc()) {
    $months[] = $row['month'];
}
// Default to the current month if none is selected.
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monthly Inventory View</title>
    <style>
        /* Basic styles for clarity */
        body { font-family: Arial, sans-serif; margin: 20px; }
        select { font-size: 1em; padding: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ccc; }
        th, td { padding: 8px; text-align: center; }
    </style>
    <script>
        // This function loads the month content using AJAX.
        function loadMonthContent() {
            var monthSelect = document.getElementById('month');
            var selectedMonth = monthSelect.value;  // e.g., "2025-03"
            console.log("loadMonthContent triggered, selectedMonth =", selectedMonth);
            // Build URL to ajax file, passing the selected month.
            var url = "ajax_view_month_content.php?month=" + encodeURIComponent(selectedMonth);
            console.log("AJAX URL:", url);
            var xhr = new XMLHttpRequest();
            xhr.open("GET", url, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    console.log("AJAX response status:", xhr.status);
                    if (xhr.status === 200) {
                        console.log("AJAX response received.");
                        document.getElementById("contentArea").innerHTML = xhr.responseText;
                    } else {
                        console.error("AJAX error:", xhr.statusText);
                    }
                }
            };
            xhr.send();
        }
        
        // When the page loads, attach an event listener to the month select element.
        window.onload = function() {
            console.log("Window loaded.");
            var monthSelect = document.getElementById('month');
            if (monthSelect) {
                console.log("Month select element found.");
                monthSelect.addEventListener("change", function(e) {
                    console.log("Month select changed to:", this.value);
                    loadMonthContent();
                });
            } else {
                console.error("Month select element NOT found!");
            }
            // Load content for the default/selected month.
            loadMonthContent();
        };
    </script>
</head>
<body>
    <h1>Monthly Inventory View</h1>
    <!-- Month Selector Dropdown (non-submitting form) -->
    <form id="monthForm" onsubmit="return false;">
        <label for="month">Select Month:</label>
        <select name="month" id="month">
            <?php foreach ($months as $month): ?>
                <option value="<?= htmlspecialchars($month) ?>" <?= ($month === $selected_month ? 'selected' : '') ?>>
                    <?= date('F Y', strtotime($month . '-01')) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    <!-- Content area where AJAX will load all tables -->
    <div id="contentArea"></div>
</body>
</html>
