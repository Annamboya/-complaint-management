<?php
include "../includes/auth.php";
include "../db.php";

// Ensure only users can access
if ($_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit();
}

// Get complaint ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: my_complaints.php");
    exit();
}

$complaint_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Fetch complaint details
$stmt = $conn->prepare("SELECT * FROM complaints WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $complaint_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$complaint = $result->fetch_assoc();

if (!$complaint) {
    die("Complaint not found or you do not have permission to view it.");
}

// Fetch complaint updates/comments
$stmt2 = $conn->prepare("SELECT * FROM complaint_updates WHERE complaint_id = ? ORDER BY created_at ASC");
$stmt2->bind_param("i", $complaint_id);
$stmt2->execute();
$updates_result = $stmt2->get_result();
$updates = $updates_result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Complaint - WCMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="dashboard">

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>User Panel</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="submit_complaint.php">Submit Complaint</a>
        <a href="my_complaints.php">My Complaints</a>
        <a href="../logout.php">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1>Complaint Details</h1>

        <div class="card-table">
            <table>
                <tr>
                    <th>Complaint ID:</th>
                    <td><?php echo $complaint['id']; ?></td>
                </tr>
                <tr>
                    <th>Title:</th>
                    <td><?php echo htmlspecialchars($complaint['title']); ?></td>
                </tr>
                <tr>
                    <th>Description:</th>
                    <td><?php echo nl2br(htmlspecialchars($complaint['description'])); ?></td>
                </tr>
                <tr>
                    <th>Status:</th>
                    <td>
                        <span class="badge <?php
                            if($complaint['status']=='pending') echo 'pending';
                            elseif($complaint['status']=='in-progress') echo 'in-progress';
                            elseif($complaint['status']=='resolved') echo 'resolved';
                            else echo 'danger';
                        ?>"><?php echo ucfirst($complaint['status']); ?></span>
                    </td>
                </tr>
                <tr>
                    <th>Submitted On:</th>
                    <td><?php echo date("Y-m-d H:i", strtotime($complaint['created_at'])); ?></td>
                </tr>
            </table>
        </div>

        <h2>Updates / Comments</h2>
        <?php if(empty($updates)): ?>
            <p>No updates yet. Please check back later.</p>
        <?php else: ?>
            <?php foreach($updates as $update): ?>
                <div class="update-card">
                    <p><strong><?php echo htmlspecialchars($update['updated_by']); ?>:</strong> <?php echo nl2br(htmlspecialchars($update['message'])); ?></p>
                    <p>Status: <span class="badge <?php
                        if($update['status']=='pending') echo 'pending';
                        elseif($update['status']=='in-progress') echo 'in-progress';
                        elseif($update['status']=='resolved') echo 'resolved';
                        else echo 'danger';
                    ?>"><?php echo ucfirst($update['status']); ?></span></p>
                    <p class="mini"><?php echo date("Y-m-d H:i", strtotime($update['created_at'])); ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <a href="my_complaints.php" class="btn secondary">Back to My Complaints</a>

    </div>
</div>

</body>
</html>