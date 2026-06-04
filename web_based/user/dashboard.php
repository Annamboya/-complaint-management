<?php
include "../includes/auth.php";
include "../db.php";

// Ensure only 'user' role can access
if ($_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch complaint counts
$counts = ['pending'=>0, 'in-progress'=>0, 'resolved'=>0, 'rejected'=>0];
$stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM complaints WHERE user_id=? GROUP BY status");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $status = strtolower($row['status']);
    $counts[$status] = $row['count'];
}

// Fetch recent complaints
$stmt = $conn->prepare("SELECT * FROM complaints WHERE user_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$complaints_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard - WCMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="dashboard">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2>User Panel</h2>
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="submit_complaint.php">Submit Complaint</a>
        <a href="my_complaints.php">My Complaints</a>
        <a href="../logout.php">Logout</a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">

        <!-- TOPBAR / WELCOME -->
        <div class="topbar">
            <h1>Welcome, <?php echo $_SESSION['name']; ?> 👋</h1>
            <p>Role: <?php echo ucfirst($_SESSION['role']); ?></p>
        </div>

        <!-- DASHBOARD STAT CARDS -->
        <div class="dashboard-cards">
            <div class="stat-card pending">
                <h2><?php echo $counts['pending']; ?></h2>
                <p>Pending Complaints</p>
            </div>
            <div class="stat-card in-progress">
                <h2><?php echo $counts['in-progress']; ?></h2>
                <p>In-Progress Complaints</p>
            </div>
            <div class="stat-card resolved">
                <h2><?php echo $counts['resolved']; ?></h2>
                <p>Resolved Complaints</p>
            </div>
            <div class="stat-card rejected">
                <h2><?php echo $counts['rejected']; ?></h2>
                <p>Rejected Complaints</p>
            </div>
        </div>

        <!-- COMPLAINTS TABLE -->
        <div class="card-table">
            <h2>Recent Complaints</h2>
            <table>
                <thead>
                    <tr>
                        <th>Complaint ID</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Submitted On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($c = $complaints_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $c['id']; ?></td>
                            <td><?php echo htmlspecialchars($c['title']); ?></td>
                            <td>
                                <span class="badge 
                                    <?php 
                                        echo $c['status']=='pending' ? 'pending' : 
                                             ($c['status']=='in-progress' ? 'in-progress' : 
                                             ($c['status']=='resolved' ? 'resolved' : 'danger')); 
                                    ?>">
                                    <?php echo ucfirst($c['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date("Y-m-d", strtotime($c['created_at'])); ?></td>
                            <td>
                                <a href="view_complaint.php?id=<?php echo $c['id']; ?>" class="action-btn view">View</a>
                                <a href="edit_complaint.php?id=<?php echo $c['id']; ?>" class="action-btn edit">Edit</a>
                                <a href="#" class="action-btn delete" data-id="<?php echo $c['id']; ?>">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
// AJAX Delete Complaint
$(document).ready(function(){
    $('.delete').click(function(e){
        e.preventDefault();
        if(confirm("Are you sure you want to delete this complaint?")){
            var id = $(this).data('id');
            $.post('delete_complaint.php', {complaint_id:id}, function(response){
                alert(response.message);
                location.reload();
            }, 'json');
        }
    });
});
</script>

</body>
</html>