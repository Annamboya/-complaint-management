<?php
include "../includes/auth.php";
include "../db.php";

if ($_SESSION['role'] !== 'admin') header("Location: ../login.php");

// Fetch complaint statistics
$total = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as total FROM complaints"))['total'];
$pending = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as total FROM complaints WHERE status='Pending'"))['total'];
$in_progress = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as total FROM complaints WHERE status='In Progress'"))['total'];
$resolved = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as total FROM complaints WHERE status='Resolved'"))['total'];
$rejected = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as total FROM complaints WHERE status='Rejected'"))['total'];

// Fetch complaints per department
$departments = mysqli_query($conn,"SELECT d.department_name, COUNT(c.id) as total 
                                   FROM departments d 
                                   LEFT JOIN complaints c ON c.department_id=d.id 
                                   GROUP BY d.id");

// Fetch audit logs
$logs = mysqli_query($conn,"SELECT l.*, u.name as admin_name 
                            FROM complaint_logs l 
                            JOIN users u ON l.performed_by=u.id 
                            ORDER BY l.action_date DESC LIMIT 50");
?>

<!DOCTYPE html>
<html>
<head>
<title>Reports - WCMS Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="dashboard">
<aside class="sidebar">
    <h2>WCMS Admin</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="view_complaints.php">All Complaints</a>
    <a href="assign_complaints.php">Assign Complaints</a>
    <a href="users.php">Users</a>
    <a href="reports.php" class="active">Reports</a>
    <a href="notifications.php">Notifications</a>
    <a href="../logout.php">Logout</a>
</aside>

<main class="main-content">
<h1>Admin Reports</h1>

<div class="dashboard-cards">
    <div class="stat-card"><h2><?php echo $total;?></h2><p>Total Complaints</p></div>
    <div class="stat-card warning"><h2><?php echo $pending;?></h2><p>Pending</p></div>
    <div class="stat-card success"><h2><?php echo $resolved;?></h2><p>Resolved</p></div>
    <div class="stat-card warning"><h2><?php echo $in_progress;?></h2><p>In Progress</p></div>
    <div class="stat-card danger"><h2><?php echo $rejected;?></h2><p>Rejected</p></div>
</div>

<h3>Complaints per Department</h3>
<canvas id="departmentChart" width="400" height="150"></canvas>

<h3>Recent Audit Logs</h3>
<table>
<thead><tr><th>Action ID</th><th>Complaint ID</th><th>Action</th><th>Performed By</th><th>Date</th></tr></thead>
<tbody>
<?php while($log=mysqli_fetch_assoc($logs)){ ?>
<tr>
<td>#<?php echo $log['id'];?></td>
<td>#<?php echo $log['complaint_id'];?></td>
<td><?php echo $log['action'];?></td>
<td><?php echo $log['admin_name'];?></td>
<td><?php echo $log['action_date'];?></td>
</tr>
<?php } ?>
</tbody>
</table>

</main>
</div>

<script>
// Prepare data for department chart
const deptLabels = [
<?php mysqli_data_seek($departments,0); while($d=mysqli_fetch_assoc($departments)){ echo "'".$d['department_name']."',"; } ?>
];
const deptData = [
<?php mysqli_data_seek($departments,0); while($d=mysqli_fetch_assoc($departments)){ echo $d['total'].","; } ?>
];

const ctx = document.getElementById('departmentChart').getContext('2d');
const departmentChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: deptLabels,
        datasets: [{
            label: 'Number of Complaints',
            data: deptData,
            backgroundColor: 'rgba(54, 162, 235, 0.6)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: { scales: { y: { beginAtZero: true } } }
});
</script>
</body>
</html>