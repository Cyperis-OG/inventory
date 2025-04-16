<?php
// index.php
// Enable error reporting for debugging purposes.
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- Meta viewport for responsive design -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Armstrong Inventory</title>
    <link rel="stylesheet" href="css/styles.css">
    <!-- Inline CSS to match the Shortcut Page look -->
    <style>
        /* Base styling matching the Shortcut Page design */
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            text-align: center;
            padding-top: 60px; /* Space for the fixed banner */
        }
        
        /* Fixed top banner with glow effect */
        .banner {
            background-color: #007acc;
            color: white;
            padding: 10px;
            font-size: 18px;
            font-weight: bold;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            overflow: hidden;
        }
        
        /* Banner link styling */
        .banner a {
            color: white;
            text-decoration: underline;
        }
        
        /* Glow effect for visual flair */
        .glow-effect {
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.8) 50%, rgba(255,255,255,0) 100%);
            animation: glow 5s infinite;
        }
        
        @keyframes glow {
            0% { left: -100%; }
            50% { left: 100%; }
            100% { left: 100%; }
        }
        
        /* Grid container for action buttons */
        .container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            max-width: 85%;
            margin: 80px auto 20px; /* Top margin to clear the fixed banner */
            gap: 20px;
            padding: 20px;
        }
        
        /* Shortcut buttons styling to match first page design */
        .shortcut-button {
            width: 200px;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            text-decoration: none;
            color: white;
            font-size: 20px;
            font-weight: bold;
            background: linear-gradient(135deg, #007acc, #005c99);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            border: none;
            outline: none;
        }
        
        .shortcut-button:hover {
            transform: scale(1.1);
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.3);
            background: linear-gradient(135deg, #0099ff, #0066cc);
        }
        
        /* Content area for AJAX-loaded partials */
        #contentSection {
            margin: 20px auto;
            max-width: 85%;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
.button-group {
  display: flex;             /* Use flexbox for layout */
  justify-content: center;   /* Center all child elements horizontally */
  align-items: center;       /* Optional: center items vertically if needed */
  gap: 20px;                 /* Provides equal spacing between the buttons */
}
    </style>
</head>
<body>
    <!-- Fixed banner at the top with glow effect -->
    <div class="banner">
        Armstrong Inventory Management
        <div class="glow-effect"></div>
    </div>

    <!-- Grid container for shortcut buttons -->
<div class="container">
  <div class="button-group">
    <!-- The buttons call functions from js/inventory.js -->
    <a href="#" class="shortcut-button" onclick="loadAddAction(); return false;">Add Action</a>
    <a href="#" class="shortcut-button" onclick="loadViewMonth(); return false;">View by Month</a>
    <a href="#" class="shortcut-button" onclick="loadYearlyTotals(); return false;">Monthly & Yearly Totals</a>
    <a href="#" class="shortcut-button" onclick="loadMaintenance(); return false;">Maintenance</a>
  </div>
</div>

    <!-- Content section where AJAX responses will be inserted -->
    <div id="contentSection"></div>

    <!-- External JavaScript containing loadAddAction, loadViewMonth, loadMaintenance, etc. -->
    <script src="js/inventory.js"></script>
<script>
// This function loads the month selection partial into #contentSection via AJAX.
function loadViewMonth() {
    // This partial only has a dropdown + empty container for month data.
    fetch('view_month_index_content.php')
        .then(response => response.text())
        .then(html => {
            // Insert the dropdown partial into the page
            document.getElementById('contentSection').innerHTML = html;

            // Now set up the default load for the newly inserted dropdown
            const monthSelect = document.getElementById('month');
            if (monthSelect) {
                // On change of the dropdown, fetch the monthly data from ajax_view_month_content
                monthSelect.addEventListener('change', () => {
                    loadSelectedMonthData();
                });

                // Initial load of the selected month
                loadSelectedMonthData();
            }
        })
        .catch(err => console.error('Error loading View by Month partial:', err));
}

// Grabs the currently selected month and loads it from ajax_view_month_content.php
function loadSelectedMonthData() {
    const monthSelect = document.getElementById('month');
    const selectedMonth = monthSelect.value;
    const url = 'ajax_view_month_content.php?month=' + encodeURIComponent(selectedMonth);

    fetch(url)
        .then(response => response.text())
        .then(html => {
            document.getElementById('monthContentArea').innerHTML = html;
        })
        .catch(err => console.error('Error loading monthly data:', err));
}  
</script>
</body>
</html>
