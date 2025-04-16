<?php
// index.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'db_connect.php';

// Fetch distinct months from the inv_actions table.
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
    <!-- You can also include an external stylesheet if desired -->
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        select {
            font-size: 1em;
            padding: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 8px;
            text-align: center;
        }
    </style>
    <script>
        // This function sends an AJAX request to load the month-specific content.
        function loadMonthlyData() {
            var monthSelect = document.getElementById("month");
            var selectedMonth = monthSelect.value; // e.g., "2025-03"
            // Build the URL with the selected month as a GET parameter.
            var url = "ajax_view_month_content.php?month=" + encodeURIComponent(selectedMonth);
            console.log("Loading URL:", url); // Debug log

            var xhr = new XMLHttpRequest();
            xhr.open("GET", url, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    console.log("AJAX response status:", xhr.status);
                    if (xhr.status === 200) {
                        document.getElementById("contentArea").innerHTML = xhr.responseText;
                    } else {
                        console.error("AJAX error:", xhr.statusText);
                        document.getElementById("contentArea").innerHTML = "<p style='color:red;'>Error loading data.</p>";
                    }
                }
            };
            xhr.send();
        }

        // Attach event listener once the DOM is ready.
        document.addEventListener("DOMContentLoaded", function() {
            var monthSelect = document.getElementById("month");
            if (monthSelect) {
                monthSelect.addEventListener("change", loadMonthlyData);
                loadMonthlyData(); // Load data for the default/selected month on page load.
            } else {
                console.error("Month select element not found!");
            }
        });
    </script>
</head>
<body>
    <h1>Monthly Inventory View</h1>
    <!-- Month Selector Dropdown -->
    <form id="monthForm" onsubmit="return false;">
        <label for="month">Select Month:</label>
        <select name="month" id="month">
            <?php foreach ($months as $month): ?>
                <option value="<?= htmlspecialchars($month) ?>" <?= ($month === $selected_month ? "selected" : "") ?>>
                    <?= date("F Y", strtotime($month . "-01")) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    <!-- Content area where AJAX will insert the monthly view -->
    <div id="contentArea"></div>
</body>
</html>
