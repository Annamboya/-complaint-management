<?php
include "../includes/auth.php";
include "../db.php";

if ($_SESSION['role'] !== 'user') {
    echo json_encode(['status'=>'error','message'=>'Access denied']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['complaint_id'])) {
        $complaint_id = intval($_POST['complaint_id']);
    } elseif (isset($_POST['id'])) {
        $complaint_id = intval($_POST['id']);
    } else {
        echo json_encode(['status'=>'error','message'=>'Missing complaint ID']);
        exit();
    }

    // Verify the complaint belongs to the user
    $stmt = $conn->prepare("SELECT * FROM complaints WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $complaint_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['status'=>'error','message'=>'Complaint not found']);
        exit();
    }

    // Log deletion
    $log_stmt = $conn->prepare("INSERT INTO complaint_logs (complaint_id, action, performed_by) VALUES (?, ?, ?)");
    $action_msg = "Complaint deleted by user";
    $log_stmt->bind_param("isi", $complaint_id, $action_msg, $_SESSION['user_id']);
    $log_stmt->execute();

    // Delete the complaint
    $del_stmt = $conn->prepare("DELETE FROM complaints WHERE id=? AND user_id=?");
    $del_stmt->bind_param("ii", $complaint_id, $_SESSION['user_id']);

    if ($del_stmt->execute()) {
        echo json_encode(['status'=>'success','message'=>'Complaint deleted successfully']);
    } else {
        echo json_encode(['status'=>'error','message'=>'Failed to delete complaint']);
    }
    exit();
}
?>