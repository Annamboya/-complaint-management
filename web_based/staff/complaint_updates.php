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
$complaint_id = intval($_GET['id']);

// Check if this complaint is assigned to this staff
$stmt = $conn->prepare("SELECT * FROM complaints WHERE id=? AND assigned_to=?");
$stmt->bind_param("ii", $complaint_id, $staff_id);
$stmt->execute();
$complaint = $stmt->get_result()->fetch_assoc();
if(!$complaint) die("Complaint not found or not assigned to you.");

// Handle new update submission
$error = $success = "";
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $message = trim($_POST['message']);
    if ($message != "") {
        $stmt2 = $conn->prepare("INSERT INTO complaint_updates (complaint_id, updated_by, message, status) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("isss", $complaint_id, $_SESSION['name'], $message, $complaint['status']);
        if($stmt2->execute()){
            $success = "Update added successfully.";
        } else {
            $error = "Failed to add update: ".$conn->error;
        }
    } else {
        $error = "Message cannot be empty.";
    }
}

// Fetch all updates
$updates = mysqli_query($conn, "SELECT * FROM complaint_updates WHERE complaint_id=$complaint_id ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Complaint Updates #<?php echo $complaint_id;?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="form-wrapper">
<h2>Updates for Complaint #<?php echo $complaint_id;?></h2>
<?php if($error) echo "<div class='error-msg'>$error</div>"; ?>
<?php if($success) echo "<div class='success-msg'>$success</div>"; ?>

<form method="POST">
    <div class="input-group">
        <label>Add Update</label>
        <textarea name="message" rows="4" required></textarea>
    </div>
    <button type="submit" class="btn primary">Add Update</button>
</form>

<h3>All Updates</h3>
<table>
    <thead>
        <tr><th>By</th><th>Message</th><th>Status</th><th>Date</th></tr>
    </thead>
    <tbody>
        <?php while($u = mysqli_fetch_assoc($updates)) { ?>
        <tr>
            <td><?php echo htmlspecialchars($u['updated_by']); ?></td>
            <td><?php echo htmlspecialchars($u['message']); ?></td>
            <td><?php echo $u['status']; ?></td>
            <td><?php echo $u['created_at']; ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
<a href="view_complaints.php" class="btn">Back to Complaints</a>
</div>
</body>
</html>