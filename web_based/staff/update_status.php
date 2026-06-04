<?php
include "../includes/auth.php";
include "../db.php";

if ($_SESSION['role'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

$staff_id = $_SESSION['user_id'];

// Validate complaint ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) die("Invalid Complaint ID.");
$id = intval($_GET['id']);

// Fetch complaint assigned to this staff
$stmt = $conn->prepare("SELECT * FROM complaints WHERE id=? AND assigned_to=?");
$stmt->bind_param("ii",$id,$staff_id);
$stmt->execute();
$complaint = $stmt->get_result()->fetch_assoc();
if(!$complaint) die("Complaint not found or not assigned to you.");

$success = $error = "";

if($_SERVER['REQUEST_METHOD']=='POST'){
    $status = $_POST['status'];
    $stmt2 = $conn->prepare("UPDATE complaints SET status=?, resolved_at=IF(?='Resolved', NOW(), resolved_at) WHERE id=?");
    $stmt2->bind_param("ssi",$status,$status,$id);
    if($stmt2->execute()){
        $success = "Status updated successfully.";

        // Log update
        $action_text = "Updated status to $status";
        $log_stmt = $conn->prepare("INSERT INTO complaint_logs (complaint_id, action, performed_by) VALUES (?, ?, ?)");
        $log_stmt->bind_param("isi",$id,$action_text,$staff_id);
        $log_stmt->execute();
    } else {
        $error = "Error: ".$conn->error;
    }

    // Refresh complaint
    $stmt->execute();
    $complaint = $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Complaint Status</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="form-wrapper">
<h2>Update Complaint #<?php echo $id;?></h2>
<?php if($error) echo "<div class='error-msg'>$error</div>"; ?>
<?php if($success) echo "<div class='success-msg'>$success</div>"; ?>

<form method="POST">
    <div class="input-group">
        <label>Status</label>
        <select name="status" required>
            <option value="Pending" <?php if($complaint['status']=='Pending') echo "selected"; ?>>Pending</option>
            <option value="In Progress" <?php if($complaint['status']=='In Progress') echo "selected"; ?>>In Progress</option>
            <option value="Resolved" <?php if($complaint['status']=='Resolved') echo "selected"; ?>>Resolved</option>
            <option value="Rejected" <?php if($complaint['status']=='Rejected') echo "selected"; ?>>Rejected</option>
        </select>
    </div>
    <button type="submit" class="btn primary">Update</button>
</form>
<a href="view_complaints.php" class="btn">Back</a>
</div>
</body>
</html>