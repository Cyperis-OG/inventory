<?php
include 'db_connect.php';

// Query the inv_rates table for unique projects.
$sql = "SELECT id, order_number, job_name, handling_in_out_pallet, inspection_rate_hr, storage_rate_pallet, handling_in_out_weight, storage_rate_weight FROM inv_rates ORDER BY order_number ASC";
$result = $conn->query($sql);
?>

<h3>Delete Projects</h3>
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>Order #</th>
            <th>Job Name</th>
            <th>Handling In/Out (Pallet Rate)</th>
            <th>Inspection Rate (per hr)</th>
            <th>Storage Rate (Pallet)</th>
            <th>Handling In/Out (Weight Rate)</th>
            <th>Storage Rate (Weight)</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['order_number']) ?></td>
                <td><?= htmlspecialchars($row['job_name']) ?></td>
                <td><?= htmlspecialchars($row['handling_in_out_pallet']) ?></td>
                <td><?= htmlspecialchars($row['inspection_rate_hr']) ?></td>
                <td><?= htmlspecialchars($row['storage_rate_pallet']) ?></td>
                <td><?= htmlspecialchars($row['handling_in_out_weight']) ?></td>
                <td><?= htmlspecialchars($row['storage_rate_weight']) ?></td>
                <td>
                    <button onclick="deleteProject(<?= htmlspecialchars($row['id']) ?>)">Delete</button>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php $conn->close(); ?>
