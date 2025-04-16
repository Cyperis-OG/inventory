<?php
include 'db_connect.php';

// Fetch unique order numbers and job names from actions
$sql = "SELECT DISTINCT order_number, job_name FROM inv_actions";
$result = $conn->query($sql);
?>

<!-- Inline styling for demonstration. In production, consider moving these styles to styles.css -->
<style>
    /* Container for each rate form */
    .rate-form-container {
        background: #f9f9f9;
        padding: 20px;
        margin: 20px auto;
        max-width: 800px;
        border-radius: 8px;
        box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.1);
    }
    
    /* Styling for the form header */
    .rate-form-container h3 {
        margin-top: 0;
        font-family: Arial, sans-serif;
        color: #333;
    }
    
    /* Styling for each form group (label and input pairing) */
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .form-group input {
        width: 100%;
        padding: 8px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }
    
    /* Gradient styling for the Save button */
    .rate-form-container button {
        background: linear-gradient(135deg, #007acc, #005c99);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        font-size: 16px;
        cursor: pointer;
        transition: transform 0.3s ease, background 0.3s ease;
    }
    
    .rate-form-container button:hover {
        transform: scale(1.05);
        background: linear-gradient(135deg, #0099ff, #0066cc);
    }
    
    /* Optional horizontal rule styling for separation between forms */
    hr {
        border: none;
        border-top: 1px solid #ccc;
        margin: 20px 0;
    }
</style>

<?php while ($row = $result->fetch_assoc()):
    $order_number = $row['order_number'];
    $job_name = $row['job_name'];

    // Check if rates exist for the given order/job
    $rates_query = "SELECT * FROM inv_rates WHERE order_number=? AND job_name=?";
    $stmt = $conn->prepare($rates_query);
    $stmt->bind_param('ss', $order_number, $job_name);
    $stmt->execute();
    $rates_result = $stmt->get_result();

    // If no rates exist, insert default rates first
    if ($rates_result->num_rows == 0) {
        $insert_query = "INSERT INTO inv_rates (order_number, job_name) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param('ss', $order_number, $job_name);
        $insert_stmt->execute();
        $insert_stmt->close();

        // Retrieve the newly inserted row
        $stmt->execute();
        $rates_result = $stmt->get_result();
    }

    $rates = $rates_result->fetch_assoc();
    $stmt->close();
?>
    <div class="rate-form-container">
        <form id="rateForm-<?=$rates['id']?>">
            <h3>Order: <?=$order_number?> - <?=$job_name?></h3>
            <input type="hidden" name="id" value="<?=$rates['id']?>">
            
            <div class="form-group">
                <label>Handling In/Out Pallet:</label>
                <input type="text" name="handling_in_out_pallet" value="<?=$rates['handling_in_out_pallet']?>">
            </div>
            
            <div class="form-group">
                <label>Inspection Rate/hr:</label>
                <input type="number" name="inspection_rate_hr" value="<?=$rates['inspection_rate_hr']?>">
            </div>
            
            <div class="form-group">
                <label>Storage Rate Pallet:</label>
                <input type="number" name="storage_rate_pallet" value="<?=$rates['storage_rate_pallet']?>">
            </div>
            
            <div class="form-group">
                <label>Handling In/Out Weight:</label>
                <input type="number" name="handling_in_out_weight" value="<?=$rates['handling_in_out_weight']?>">
            </div>
            
            <div class="form-group">
                <label>Storage Rate Weight:</label>
                <input type="number" name="storage_rate_weight" value="<?=$rates['storage_rate_weight']?>">
            </div>
            
            <button type="button" onclick="saveProjectRates('<?=$rates['id']?>')">Save</button>
        </form>
    </div>
    <hr>
<?php endwhile; ?>

<?php $conn->close(); ?>
