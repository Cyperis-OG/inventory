<?php
include 'db_connect.php';

$sql = "SELECT * FROM inv_actions WHERE 
    (action_type IN ('Handling In', 'Handling Out') AND (quantity IS NULL OR weight IS NULL OR type IS NULL)) OR
    (action_type='Open Inspect' AND (hours IS NULL OR type IS NULL))";

$result = $conn->query($sql);
?>

<h3>Incomplete Action Entries</h3>

<table>
<tr>
<th>ID</th><th>Action</th><th>Missing Field</th><th>Update</th>
</tr>
<?php while($row = $result->fetch_assoc()): 
    $missing_fields = [];
    if (in_array($row['action_type'], ['Handling In', 'Handling Out'])) {
        if (!$row['quantity']) $missing_fields[] = 'quantity';
        if (!$row['weight']) $missing_fields[] = 'weight';
        if (!$row['type']) $missing_fields[] = 'type';
    } else if ($row['action_type'] == 'Open Inspect') {
        if (!$row['hours']) $missing_fields[] = 'hours';
        if (!$row['type']) $missing_fields[] = 'type';
    }
    foreach ($missing_fields as $field): ?>
    <tr>
        <td><?=$row['id']?></td>
        <td><?=$row['action_type']?></td>
        <td><?=$field?></td>
        <td>
            <input type="text" onchange="saveActionUpdate('<?=$row['id']?>','<?=$field?>',this.value)">
        </td>
    </tr>
    <?php endforeach; ?>
<?php endwhile; ?>
</table>
