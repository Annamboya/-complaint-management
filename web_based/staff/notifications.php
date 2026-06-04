<?php
include "../includes/auth.php";
include "../db.php";

if ($_SESSION['role'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

$staff_id = $_SESSION['user_id'];

// Mark notification as read
if(isset($_GET['read']) && is_numeric($_GET['read'])){
    $nid = intval($_GET['read']);
    mysqli_query($conn, "UPDATE notifications SET status='read' WHERE id=$nid AND user_id=$staff_id");
}

// Fetch all notifications
$notifications = mysqli_query($conn, "
    SELECT * FROM notifications
    WHERE user_id=$staff_id
    ORDER BY created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Notifications - WCMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="dashboard">
    <aside class="sidebar">
        <h2>Staff Dashboard</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="view_complaints.php">My Complaints</a>
        <a href="notifications.php" class="active">Notifications</a>
        <a href="../logout.php">Logout</a>
    </aside>
    <main class="main-content">
        <h1>Notifications</h1>
        <ul>
            <?php while($n = mysqli_fetch_assoc($notifications)) { ?>
            <li style="<?php echo $n['status']=='unread' ? 'font-weight:bold;' : ''; ?>">
                <?php echo htmlspecialchars($n['message']); ?>
                <small>(<?php echo $n['created_at']; ?>)</small>
                <?php if($n['status']=='unread'){ ?>
                    - <a href="?read=<?php echo $n['id']; ?>">Mark as read</a>
                <?php } ?>
            </li>
            <?php } ?>
        </ul>
    </main>
</div>
</body>
</html>