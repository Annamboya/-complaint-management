<?php
include "../includes/auth.php";
include "../db.php";

if ($_SESSION['role'] !== 'admin') header("Location: ../login.php");

// Mark notifications as read if requested
if(isset($_GET['mark_read'])){
    $nid = intval($_GET['mark_read']);
    mysqli_query($conn,"UPDATE notifications SET status='read' WHERE id=$nid");
    header("Location: notifications.php");
}

// Fetch notifications
$notifications = mysqli_query($conn,"SELECT n.*, u.name as user_name 
                                     FROM notifications n 
                                     JOIN users u ON n.user_id=u.id 
                                     ORDER BY n.created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Notifications - WCMS Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="dashboard">
<aside class="sidebar">
    <h2>WCMS Admin</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="view_complaints.php">All Complaints</a>
    <a href="assign_complaints.php">Assign Complaints</a>
    <a href="users.php">Users</a>
    <a href="reports.php">Reports</a>
    <a href="notifications.php" class="active">Notifications</a>
    <a href="../logout.php">Logout</a>
</aside>

<main class="main-content">
<h1>Notifications</h1>
<table>
<thead><tr><th>ID</th><th>User</th><th>Message</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
<tbody>
<?php while($n=mysqli_fetch_assoc($notifications)){ ?>
<tr>
<td>#<?php echo $n['id'];?></td>
<td><?php echo $n['user_name'];?></td>
<td><?php echo $n['message'];?></td>
<td><?php echo ucfirst($n['status']);?></td>
<td><?php echo $n['created_at'];?></td>
<td>
<?php if($n['status']=='unread'){ ?>
<a href="notifications.php?mark_read=<?php echo $n['id'];?>">Mark as Read</a>
<?php } else { echo "—"; } ?>
</td>
</tr>
<?php } ?>
</tbody>
</table>
</main>
</div>
</body>
</html>