<?php
include "../includes/auth.php";
include "../db.php";

if ($_SESSION['role'] !== 'admin') header("Location: ../login.php");

// Stats
$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM complaints"))['count'];
$resolved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM complaints WHERE status='Resolved'"))['count'];
$pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM complaints WHERE status='Pending'"))['count'];
$progress = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM complaints WHERE status='In Progress'"))['count'];

// Recent complaints
$recent = mysqli_query($conn, "SELECT c.*, u.name AS user_name FROM complaints c JOIN users u ON c.user_id=u.id ORDER BY c.created_at DESC LIMIT 5");

// Users
$users = mysqli_query($conn, "SELECT * FROM users ORDER BY role ASC, name ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - WCMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="dashboard">
    <aside class="sidebar">
        <h2>WCMS Admin</h2>
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="view_complaints.php">All Complaints</a>
        <a href="assign_complaints.php">Assign Complaints</a>
        <a href="users.php">Users</a>
        <a href="reports.php">Reports</a>
        <a href="../logout.php">Logout</a>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <h1>Admin Dashboard</h1>
            <span>Welcome, <?php echo $_SESSION['name']; ?> 👋</span>
        </div>

        <!-- Stats Cards -->
        <div class="dashboard-cards">
            <div class="stat-card"><h2><?php echo $total; ?></h2><p>Total Complaints</p></div>
            <div class="stat-card success"><h2><?php echo $resolved; ?></h2><p>Resolved</p></div>
            <div class="stat-card warning"><h2><?php echo $progress; ?></h2><p>In Progress</p></div>
            <div class="stat-card danger"><h2><?php echo $pending; ?></h2><p>Pending</p></div>
        </div>

        <!-- Recent Complaints -->
        <div class="card-table">
            <h3>Recent Complaints</h3>
            <table>
                <thead><tr><th>ID</th><th>Title</th><th>User</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($recent)) { ?>
                    <tr>
                        <td>#<?php echo $row['id']; ?></td>
                        <td><?php echo $row['title']; ?></td>
                        <td><?php echo $row['user_name']; ?></td>
                        <td><span class="badge <?php echo strtolower(str_replace(' ', '-', $row['status'])); ?>"><?php echo $row['status']; ?></span></td>
                        <td><?php echo $row['created_at']; ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <a href="view_complaints.php" class="btn primary">View All</a>
        </div>

        <!-- Users Management -->
        <div class="card-table">
            <h3>Manage Users</h3>
            <table>
                <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Department</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php while($u = mysqli_fetch_assoc($users)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u['name']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo ucfirst($u['role']); ?></td>
                        <td><?php echo $u['department_id'] ? mysqli_fetch_assoc(mysqli_query($conn,"SELECT department_name FROM departments WHERE id=".$u['department_id']))['department_name'] : "-"; ?></td>
                        <td>
                            <?php if($u['id'] != $_SESSION['user_id']) { ?>
                                <a href="edit_user.php?id=<?php echo $u['id']; ?>">Edit</a> | 
                                <a href="delete_user.php?id=<?php echo $u['id']; ?>" class="delete-user">Delete</a>
                            <?php } else { echo "—"; } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <a href="add_user.php" class="btn primary">Add New User</a>
        </div>
    </main>
</div>

<script>
$(document).ready(function(){
    $('.delete-user').click(function(e){
        e.preventDefault();
        if(confirm("Are you sure you want to delete this user?")){
            window.location = $(this).attr('href');
        }
    });
});
</script>
</body>
</html>
<?php include "../includes/footer.php"; ?>