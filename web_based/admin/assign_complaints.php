<?php
include "../includes/auth.php";
include "../db.php";

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch complaints with user names and assigned staff names
$complaints_query = "
    SELECT c.*, u.name as user_name, s.name as staff_name
    FROM complaints c
    JOIN users u ON c.user_id = u.id
    LEFT JOIN users s ON c.assigned_to = s.id
    ORDER BY c.created_at DESC
";
$complaints = mysqli_query($conn, $complaints_query);

// Define status badge classes
$status_classes = [
    'Pending' => 'badge-warning',
    'In Progress' => 'badge-primary',
    'Resolved' => 'badge-success',
    'Rejected' => 'badge-danger'
];
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Complaints - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f4f4f4; }
        .badge { padding: 4px 8px; border-radius: 4px; color: #fff; font-weight: bold; }
        .badge-warning { background-color: #f0ad4e; }
        .badge-primary { background-color: #0275d8; }
        .badge-success { background-color: #5cb85c; }
        .badge-danger { background-color: #d9534f; }
        .btn { padding: 6px 12px; text-decoration: none; border-radius: 4px; margin-right: 4px; }
        .btn-primary { background-color: #0275d8; color: #fff; }
        .btn-success { background-color: #5cb85c; color: #fff; }
        .btn-danger { background-color: #d9534f; color: #fff; }
    </style>
</head>
<body>
<div class="dashboard">
    <main class="main-content">
        <h2>All Complaints</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>User</th>
                    <th>Assigned To</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Date Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($c = mysqli_fetch_assoc($complaints)) { ?>
                <tr>
                    <td>#<?php echo $c['id']; ?></td>
                    <td><?php echo htmlspecialchars($c['title']); ?></td>
                    <td><?php echo htmlspecialchars($c['user_name']); ?></td>
                    <td>
                        <?php echo $c['assigned_to'] ? htmlspecialchars($c['staff_name']) : "<em>Unassigned</em>"; ?>
                    </td>
                    <td>
                        <span class="badge <?php echo $status_classes[$c['status']] ?? 'badge-warning'; ?>">
                            <?php echo $c['status']; ?>
                        </span>
                    </td>
                    <td><?php echo $c['priority']; ?></td>
                    <td><?php echo $c['created_at']; ?></td>
                    <td>
                        <a href="edit_complaint.php?id=<?php echo $c['id']; ?>" class="btn btn-primary">Edit</a>
                        <a href="assign_complaints.php?id=<?php echo $c['id']; ?>" class="btn btn-success">Assign</a>
                        <a href="delete_complaint.php?id=<?php echo $c['id']; ?>" class="btn btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this complaint?');">Delete</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <a href="dashboard.php" class="btn btn-primary" style="margin-top: 20px; display:inline-block;">Back to Dashboard</a>
    </main>
</div>
</body>
</html>