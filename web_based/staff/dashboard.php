<?php
include "../includes/auth.php";
include "../db.php";

// Ensure staff access only
if ($_SESSION['role'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

$staff_id = $_SESSION['user_id'];

// ================= HANDLE TAKE OVER =================
if(isset($_GET['take_over'])){
    $complaint_id = intval($_GET['take_over']);
    $conn->query("
        UPDATE complaints 
        SET assigned_to = $staff_id, status='In Progress'
        WHERE id = $complaint_id AND (assigned_to IS NULL OR assigned_to=0)
    ");
    // Increment staff workload
    $conn->query("UPDATE users SET total_assigned = total_assigned + 1 WHERE id = $staff_id");
    header("Location: dashboard.php");
    exit();
}

// ================= STATS =================
$total_assigned = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM complaints WHERE assigned_to=$staff_id"))['count'];
$resolved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM complaints WHERE assigned_to=$staff_id AND status='Resolved'"))['count'];
$pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM complaints WHERE assigned_to=$staff_id AND status='Pending'"))['count'];
$in_progress = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM complaints WHERE assigned_to=$staff_id AND status='In Progress'"))['count'];

// ================= RECENT ASSIGNED COMPLAINTS =================
$recent_complaints = mysqli_query($conn, "
    SELECT c.*, u.name as user_name 
    FROM complaints c
    JOIN users u ON c.user_id = u.id
    WHERE c.assigned_to = $staff_id
    ORDER BY c.created_at DESC
    LIMIT 5
");

// ================= UNATTENDED COMPLAINTS =================
$unattended = mysqli_query($conn, "
    SELECT c.*, u.name as user_name 
    FROM complaints c
    JOIN users u ON c.user_id = u.id
    WHERE c.status='Pending' AND TIMESTAMPDIFF(MINUTE, c.created_at, NOW()) > 30
    ORDER BY c.created_at ASC
");

// ================= NOTIFICATIONS =================
$notifications = mysqli_query($conn, "
    SELECT * FROM notifications
    WHERE user_id = $staff_id
    ORDER BY created_at DESC
    LIMIT 5
");

// Status badge classes
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
    <title>Staff Dashboard - WCMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .dashboard-cards { display:flex; gap:15px; margin-top:20px; flex-wrap: wrap; }
        .stat-card { flex:1; padding:20px; background:#f4f4f4; border-radius:8px; text-align:center; }
        .stat-card h2 { margin:0; font-size:28px; }
        .stat-card p { margin:5px 0 0; }
        .stat-card.success { background:#5cb85c; color:#fff; }
        .stat-card.warning { background:#f0ad4e; color:#fff; }
        .stat-card.primary { background:#0275d8; color:#fff; }
        .badge { padding:4px 8px; border-radius:4px; color:#fff; font-weight:bold; }
        .badge-warning { background-color:#f0ad4e; }
        .badge-primary { background-color:#0275d8; }
        .badge-success { background-color:#5cb85c; }
        .badge-danger { background-color:#d9534f; }
        table { width:100%; border-collapse: collapse; margin-top:15px; }
        th, td { padding:10px; border:1px solid #ddd; text-align:left; }
        th { background-color:#f4f4f4; }
        .btn { padding:6px 12px; border-radius:4px; text-decoration:none; margin-right:4px; display:inline-block; }
        .btn-primary { background-color:#0275d8; color:#fff; }
        .btn-success { background-color:#5cb85c; color:#fff; }
        .btn-danger { background-color:#d9534f; color:#fff; }
        ul { padding-left: 20px; }
    </style>
</head>
<body>
<div class="dashboard">
    <aside class="sidebar">
        <h2>WCMS Staff</h2>
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="view_complaints.php">My Complaints</a>
        <a href="notifications.php">Notifications</a>
        <a href="../logout.php">Logout</a>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <h1>Staff Dashboard</h1>
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?> 👋</span>
        </div>

        <!-- Stats Cards -->
        <div class="dashboard-cards">
            <div class="stat-card"><h2><?php echo $total_assigned; ?></h2><p>Total Assigned</p></div>
            <div class="stat-card success"><h2><?php echo $resolved; ?></h2><p>Resolved</p></div>
            <div class="stat-card primary"><h2><?php echo $in_progress; ?></h2><p>In Progress</p></div>
            <div class="stat-card warning"><h2><?php echo $pending; ?></h2><p>Pending</p></div>
        </div>

        <!-- Recent Assigned Complaints -->
        <div class="card-table">
            <h3>Recent Assigned Complaints</h3>
            <table>
                <thead>
                    <tr><th>ID</th><th>Title</th><th>User</th><th>Status</th><th>Priority</th><th>Date</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($recent_complaints)) { ?>
                    <tr>
                        <td>#<?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                        <td><span class="badge <?php echo $status_classes[$row['status']] ?? 'badge-warning'; ?>"><?php echo $row['status']; ?></span></td>
                        <td><?php echo $row['priority']; ?></td>
                        <td><?php echo $row['created_at']; ?></td>
                        <td>
                            <a href="edit_complaint.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">Update</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <a href="view_complaints.php" class="btn primary">View All</a>
        </div>

        <!-- Unattended Complaints -->
        <div class="card-table">
            <h3>Unattended Complaints (Older than 30 min)</h3>
            <table>
                <thead>
                    <tr><th>ID</th><th>Title</th><th>User</th><th>Status</th><th>Date</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($unattended)) { ?>
                    <tr>
                        <td>#<?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                        <td><span class="badge badge-warning"><?php echo $row['status']; ?></span></td>
                        <td><?php echo $row['created_at']; ?></td>
                        <td>
                            <a href="dashboard.php?take_over=<?php echo $row['id']; ?>" class="btn btn-success">Take Over</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- Notifications -->
        <div class="card-table">
            <h3>Recent Notifications</h3>
            <ul>
                <?php while($notif = mysqli_fetch_assoc($notifications)) { ?>
                    <li>
                        <?php echo htmlspecialchars($notif['message']); ?> 
                        <small style="color:#888;">(<?php echo $notif['created_at']; ?>)</small>
                    </li>
                <?php } ?>
            </ul>
            <a href="notifications.php" class="btn primary">View All Notifications</a>
        </div>
    </main>
</div>
</body>
</html>