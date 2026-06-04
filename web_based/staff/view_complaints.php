<?php
include "../includes/auth.php";
include "../db.php";

if ($_SESSION['role'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

$staff_id = $_SESSION['user_id'];

// Fetch assigned complaints
$complaints = mysqli_query($conn, "
    SELECT c.*, u.name AS user_name, d.department_name 
    FROM complaints c
    JOIN users u ON c.user_id = u.id
    LEFT JOIN departments d ON c.department_id = d.id
    WHERE c.assigned_to = $staff_id
    ORDER BY c.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Complaints - WCMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="dashboard">
    <aside class="sidebar">
        <h2>Staff Dashboard</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="view_complaints.php" class="active">My Complaints</a>
        <a href="notifications.php">Notifications</a>
        <a href="../logout.php">Logout</a>
    </aside>
    <main class="main-content">
        <h1>My Complaints</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>User</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($c = mysqli_fetch_assoc($complaints)) { 
                    $status_class = strtolower(str_replace(' ', '-', $c['status']));
                ?>
                <tr>
                    <td>#<?php echo $c['id']; ?></td>
                    <td><?php echo htmlspecialchars($c['title']); ?></td>
                    <td><?php echo htmlspecialchars($c['user_name']); ?></td>
                    <td><?php echo $c['department_name'] ?: "-"; ?></td>
                    <td><span class="badge <?php echo $status_class; ?>"><?php echo $c['status']; ?></span></td>
                    <td><?php echo $c['priority']; ?></td>
                    <td>
                        <a href="update_status.php?id=<?php echo $c['id']; ?>" class="btn btn-primary">Update Status</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </main>
</div>
</body>
</html>