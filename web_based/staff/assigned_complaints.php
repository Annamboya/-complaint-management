<?php
include "../includes/auth.php";
include "../db.php";

if ($_SESSION['role'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

$staff_id = $_SESSION['user_id'];

// Fetch all complaints assigned to staff with status highlights
$complaints = mysqli_query($conn, "
    SELECT c.*, u.name AS user_name
    FROM complaints c
    JOIN users u ON c.user_id = u.id
    WHERE c.assigned_to = $staff_id
    ORDER BY c.status ASC, c.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assigned Complaints - WCMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="dashboard">
    <aside class="sidebar">
        <h2>Staff Dashboard</h2>
        <a href="staff_dashboard.php">Dashboard</a>
        <a href="assigned_complaints.php" class="active">Assigned Complaints</a>
        <a href="notifications.php">Notifications</a>
        <a href="../logout.php">Logout</a>
    </aside>
    <main class="main-content">
        <h1>Assigned Complaints</h1>
        <table>
            <thead>
                <tr><th>ID</th><th>Title</th><th>User</th><th>Status</th><th>Priority</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php while($c = mysqli_fetch_assoc($complaints)) { 
                    $status_class = strtolower(str_replace(' ', '-', $c['status']));
                ?>
                <tr>
                    <td>#<?php echo $c['id']; ?></td>
                    <td><?php echo htmlspecialchars($c['title']); ?></td>
                    <td><?php echo htmlspecialchars($c['user_name']); ?></td>
                    <td><span class="badge <?php echo $status_class; ?>"><?php echo $c['status']; ?></span></td>
                    <td><?php echo $c['priority']; ?></td>
                    <td>
                        <a href="update_status.php?id=<?php echo $c['id']; ?>" class="btn btn-primary">Update</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </main>
</div>
</body>
</html>