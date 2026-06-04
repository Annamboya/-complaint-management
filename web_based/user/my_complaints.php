<?php
include "../includes/auth.php";

// Ensure only users can access
if ($_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit();
}

include "../db.php";

// Fetch complaints of current user
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM complaints WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$complaints = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Complaints - WCMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="dashboard">

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>User Panel</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="submit_complaint.php">Submit Complaint</a>
        <a href="my_complaints.php" class="active">My Complaints</a>
        <a href="../logout.php">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1>My Complaints</h1>

        <?php if(empty($complaints)): ?>
            <p>You have not submitted any complaints yet.</p>
        <?php else: ?>
            <div class="card-table">
                <table>
                    <thead>
                        <tr>
                            <th>Complaint ID</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Submitted On</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($complaints as $c): ?>
                            <tr>
                                <td><?php echo $c['id']; ?></td>
                                <td><?php echo htmlspecialchars($c['title']); ?></td>
                                <td>
                                    <span class="badge <?php
                                        if($c['status']=='pending') echo 'pending';
                                        elseif($c['status']=='in-progress') echo 'in-progress';
                                        elseif($c['status']=='resolved') echo 'resolved';
                                        else echo 'danger';
                                    ?>"><?php echo ucfirst($c['status']); ?></span>
                                </td>
                                <td><?php echo date("Y-m-d", strtotime($c['created_at'])); ?></td>
                                <td><a href="view_complaint.php?id=<?php echo $c['id']; ?>">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>