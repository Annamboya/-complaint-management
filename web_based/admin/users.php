<?php
include "../includes/auth.php";
include "../db.php";

if ($_SESSION['role'] !== 'admin') header("Location: ../login.php");

$users = mysqli_query($conn, "SELECT * FROM users ORDER BY role ASC, name ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Users - WCMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="dashboard">
<aside class="sidebar">
    <h2>WCMS Admin</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="view_complaints.php">All Complaints</a>
    <a href="assign_complaints.php">Assign Complaints</a>
    <a href="users.php" class="active">Users</a>
    <a href="reports.php">Reports</a>
    <a href="../logout.php">Logout</a>
</aside>

<main class="main-content">
<h1>Users Management</h1>
<a href="add_user.php" class="btn primary">Add New User</a>
<table>
<thead>
<tr><th>Name</th><th>Email</th><th>Role</th><th>Department</th><th>Actions</th></tr>
</thead>
<tbody>
<?php while($u = mysqli_fetch_assoc($users)){ ?>
<tr>
    <td><?php echo htmlspecialchars($u['name']); ?></td>
    <td><?php echo htmlspecialchars($u['email']); ?></td>
    <td><?php echo ucfirst($u['role']); ?></td>
    <td><?php
        if($u['department_id']){
            $dpt = mysqli_fetch_assoc(mysqli_query($conn,"SELECT department_name FROM departments WHERE id=".$u['department_id']));
            echo $dpt['department_name'];
        } else { echo "-"; }
    ?></td>
    <td>
        <?php if($u['id'] != $_SESSION['user_id']){ ?>
            <a href="edit_user.php?id=<?php echo $u['id']; ?>">Edit</a> |
            <a href="delete_user.php?id=<?php echo $u['id']; ?>" class="delete-user">Delete</a>
        <?php } else { echo "—"; } ?>
    </td>
</tr>
<?php } ?>
</tbody>
</table>
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