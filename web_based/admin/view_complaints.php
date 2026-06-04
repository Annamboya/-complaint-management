<?php
include "../includes/auth.php";
include "../db.php";

if ($_SESSION['role'] !== 'admin') header("Location: ../login.php");

// Fetch all complaints with user, department, category info
$complaints = mysqli_query($conn, "
    SELECT c.*, u.name as user_name, d.department_name, cat.category_name 
    FROM complaints c
    JOIN users u ON c.user_id = u.id
    JOIN departments d ON c.department_id = d.id
    JOIN categories cat ON c.category_id = cat.id
    ORDER BY c.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Complaints - WCMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="dashboard">
<aside class="sidebar">
    <h2>WCMS Admin</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="view_complaints.php" class="active">All Complaints</a>
    <a href="assign_complaints.php">Assign Complaints</a>
    <a href="users.php">Users</a>
    <a href="reports.php">Reports</a>
    <a href="../logout.php">Logout</a>
</aside>

<main class="main-content">
<h1>All Complaints</h1>
<table>
<thead>
<tr>
<th>ID</th><th>Title</th><th>User</th><th>Department</th><th>Category</th><th>Status</th><th>Priority</th><th>Assigned To</th><th>Actions</th>
</tr>
</thead>
<tbody>
<?php while($c = mysqli_fetch_assoc($complaints)){ 
    $assigned_name = $c['assigned_to'] ? mysqli_fetch_assoc(mysqli_query($conn,"SELECT name FROM users WHERE id=".$c['assigned_to']))['name'] : "-";
?>
<tr>
<td>#<?php echo $c['id']; ?></td>
<td><?php echo htmlspecialchars($c['title']); ?></td>
<td><?php echo $c['user_name']; ?></td>
<td><?php echo $c['department_name']; ?></td>
<td><?php echo $c['category_name']; ?></td>
<td><?php echo $c['status']; ?></td>
<td><?php echo $c['priority']; ?></td>
<td><?php echo $assigned_name; ?></td>
<td>
<a href="edit_complaint.php?id=<?php echo $c['id']; ?>">Edit</a> |
<a href="delete_complaint.php?id=<?php echo $c['id']; ?>" class="delete-complaint">Delete</a> |
<a href="assign_complaints.php?id=<?php echo $c['id']; ?>">Assign</a>
</td>
</tr>
<?php } ?>
</tbody>
</table>
</main>
</div>

<script>
$(document).ready(function(){
    $('.delete-complaint').click(function(e){
        e.preventDefault();
        if(confirm("Are you sure you want to delete this complaint?")){
            window.location = $(this).attr('href');
        }
    });
});
</script>
</body>
</html>

<?php include "../includes/footer.php"; ?>