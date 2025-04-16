<?php
include 'db_connect.php';

header('Content-Type: application/json');

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid project ID.']);
    exit;
}

// Begin transaction to ensure both deletions occur together.
$conn->begin_transaction();

try {
    // Retrieve the order number and job name for the project
    $stmt = $conn->prepare("SELECT order_number, job_name FROM inv_rates WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($order, $job);
    if (!$stmt->fetch()) {
        // No record found
        $stmt->close();
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Project not found.']);
        exit;
    }
    $stmt->close();

    // Delete all action lines from inv_actions matching this order number and job name
    $stmt_del_actions = $conn->prepare("DELETE FROM inv_actions WHERE order_number = ? AND job_name = ?");
    $stmt_del_actions->bind_param("ss", $order, $job);
    $stmt_del_actions->execute();
    $stmt_del_actions->close();

    // Now delete the project from inv_rates
    $stmt_del_project = $conn->prepare("DELETE FROM inv_rates WHERE id = ?");
    $stmt_del_project->bind_param("i", $id);
    $stmt_del_project->execute();
    $stmt_del_project->close();

    // Commit transaction
    $conn->commit();

    echo json_encode(['status' => 'success', 'message' => 'Project and all related action lines deleted successfully.']);

} catch (Exception $e) {
    // Rollback on any error
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Deletion failed: ' . $e->getMessage()]);
}

$conn->close();
?>
