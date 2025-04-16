<?php include 'db_connect.php'; ?>
<!-- Inline CSS for the improved form styling -->
<style>
    /* Container styling for the form */
    #actionForm {
        margin: 20px auto;
        max-width: 600px;
        background-color: #ffffff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    }

    /* Styling for form labels */
    #actionForm label {
        display: block;
        margin: 10px 0 5px;
        font-weight: bold;
    }

    /* Styling for inputs and select boxes */
    #actionForm select,
    #actionForm input[type="date"],
    #actionForm input[type="time"],
    #actionForm input[type="text"],
    #actionForm input[type="number"] {
        width: 100%;
        padding: 8px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }

    /* Styling for the submit button similar to your shortcut buttons but in a smaller size */
    .form-button {
        background: linear-gradient(135deg, #007acc, #005c99);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        font-size: 16px;
        cursor: pointer;
        transition: transform 0.3s ease, background 0.3s ease;
        margin-top: 15px;
    }

    /* Hover effect for the submit button */
    .form-button:hover {
        transform: scale(1.05);
        background: linear-gradient(135deg, #0099ff, #0066cc);
    }
</style>

<!-- HTML Form -->
<form id="actionForm">
    <label for="action_type">Action Type:</label>
    <!-- Note: the onchange now calls renderDynamicFields() -->
    <select id="action_type" name="action_type" onchange="renderDynamicFields()">
        <option value="">Select Type</option>
        <option>Handling In</option>
        <option>Handling Out</option>
        <option>Open Inspect</option>
    </select>

    <!-- Container for dynamically generated fields -->
    <div id="dynamic_fields"></div>

    <!-- Styled submit button -->
    <button type="button" class="form-button" onclick="submitAction()">Submit Action</button>
</form>

<!-- JavaScript for dynamic fields and form submission -->
<script>
    // Function to render additional input fields based on the Action Type selected.
    function renderDynamicFields() {
        let type = document.getElementById('action_type').value;
        let fields = '';

        // Get today's date (YYYY-MM-DD format) and the current time (HH:MM).
        let today = new Date().toISOString().split('T')[0];
        let time = new Date().toLocaleTimeString('en-US', {hour12: false}).substr(0,5);

        // Common fields for all action types.
        fields += `
            <label for="action_date">Date:</label>
            <input type="date" id="action_date" name="action_date" value="${today}">
            
            <label for="action_time">Time:</label>
            <input type="time" id="action_time" name="action_time" value="${time}">
            
            <label for="order_number">Order #:</label>
            <input type="text" id="order_number" name="order_number" onblur="fetchJobName()">
            
            <label for="job_name">Job Name:</label>
            <input type="text" id="job_name" name="job_name">
        `;

        // Additional fields specific to Handling In/Out.
        if (type === 'Handling In' || type === 'Handling Out') {
            fields += `
                <label for="item_type">Type:</label>
                <select id="item_type" name="type">
                    <option>Pallets</option>
                    <option>Cartons</option>
                </select>
                
                <label for="quantity">Qty:</label>
                <input type="number" id="quantity" name="quantity">
                
                <label for="weight">Weight:</label>
                <input type="number" step="0.01" id="weight" name="weight">
            `;
        } else if (type === 'Open Inspect') { // Additional fields specific to Open Inspect.
            fields += `
                <label for="item_type">Type:</label>
                <select id="item_type" name="type">
                    <option>Pallets</option>
                    <option>Cartons</option>
                </select>
                
                <label for="hours">Hours:</label>
                <input type="number" step="0.01" id="hours" name="hours">
            `;
        }

        // Insert the dynamically built fields into the container.
        document.getElementById('dynamic_fields').innerHTML = fields;
    }

    // Function to submit the form data via AJAX.
    function submitAction() {
        const form = document.getElementById('actionForm');
        const actionType = document.getElementById('action_type').value;

        if (!actionType) {
            alert('Please select an Action Type.');
            return;
        }

        let formData = new FormData(form);
        let xhr = new XMLHttpRequest();
        xhr.open('POST', 'submit_action.php', true);
        xhr.onload = function() {
            alert(this.responseText);
            if (this.responseText.toLowerCase().includes('success')) {
                form.reset();
                document.getElementById('dynamic_fields').innerHTML = '';
            }
        };
        xhr.send(formData);
    }
</script>
