<?php
include "../includes/auth.php";
include "../db.php";

// Only admins can access
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) die("User ID missing");
$id = intval($_GET['id']);
$error = $success = "";

// Fetch user
$userStmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$userStmt->bind_param("i", $id);
$userStmt->execute();
$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();
$userStmt->close();

if (!$user) die("User not found");

// Fetch departments
$departments = $conn->query("SELECT * FROM departments ORDER BY department_name ASC");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $role = $_POST['role'];
    $department_id = $_POST['department_id'] ?: NULL;
    $newPassword = $_POST['password'] ?? '';

    // Update user info
    if ($newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $updateStmt = $conn->prepare("UPDATE users SET name=?, role=?, department_id=?, password=? WHERE id=?");
        $updateStmt->bind_param("ssisi", $name, $role, $department_id, $hashedPassword, $id);
    } else {
        $updateStmt = $conn->prepare("UPDATE users SET name=?, role=?, department_id=? WHERE id=?");
        $updateStmt->bind_param("ssii", $name, $role, $department_id, $id);
    }

    if ($updateStmt->execute()) {
        $success = "User updated successfully!";

        // Log admin action
        $action = "Updated user ID $id" . ($newPassword ? " and reset password" : "");
        $logStmt = $conn->prepare("INSERT INTO complaint_logs (complaint_id, action, performed_by) VALUES (?, ?, ?)");
        $nullComplaintId = NULL;
        $logStmt->bind_param("isi", $nullComplaintId, $action, $_SESSION['user_id']);
        $logStmt->execute();
        $logStmt->close();

    } else {
        $error = "Failed to update user!";
    }

    $updateStmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="dashboard">
    <main class="main-content">
        <h2>Edit User</h2>
        <?php if ($error) echo "<div class='error-msg'>$error</div>"; ?>
        <?php if ($success) echo "<div class='success-msg'>$success</div>"; ?>
        <form method="POST">
            <div class="input-group">
                <label>Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="input-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="user" <?php if ($user['role'] == 'user') echo 'selected'; ?>>User</option>
                    <option value="staff" <?php if ($user['role'] == 'staff') echo 'selected'; ?>>Staff</option>
                    <option value="admin" <?php if ($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                </select>
            </div>
            <div class="input-group">
                <label>Department</label>
                <select name="department_id">
                    <option value="">-- None --</option>
                    <?php while ($d = $departments->fetch_assoc()) { ?>
                        <option value="<?php echo $d['id']; ?>" <?php if ($d['id'] == $user['department_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($d['department_name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="input-group">
                <label>New Password <small>(leave blank to keep current)</small></label>
                <input type="password" name="password" placeholder="Enter new password">
            </div>
            <button type="submit" class="btn primary">Update User</button>
        </form>
    </main>
</div>
</body>
</html>