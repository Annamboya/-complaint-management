<?php
include "../includes/auth.php";
include "../db.php";

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$error = $success = "";

// Fetch departments
$departments = mysqli_query($conn, "SELECT * FROM departments ORDER BY department_name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $department_id = !empty($_POST['department_id']) ? intval($_POST['department_id']) : NULL;

    // Validate role
    $allowed_roles = ['admin', 'staff', 'user'];
    $role = $_POST['role'];
    if (!in_array($role, $allowed_roles)) {
        $error = "Invalid role selected!";
    }

    if (empty($error)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email already exists!";
        } else {
            // Insert user
            $stmt2 = $conn->prepare("INSERT INTO users (name, email, password, role, department_id, status) VALUES (?,?,?,?,?,?)");
            $status = 'active';
            $stmt2->bind_param("sssisi", $name, $email, $hashed_password, $role, $department_id, $status);

            if ($stmt2->execute()) {
                $success = "User created successfully!";

                // Optional: Add audit log
                $admin_id = $_SESSION['user_id'];
                $user_id = $stmt2->insert_id;
                mysqli_query($conn, "INSERT INTO complaint_logs (complaint_id, action, performed_by) VALUES (0, 'Created new user ID $user_id with role $role', $admin_id)");

            } else {
                $error = "Failed to create user! Error: ".$conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add User - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="dashboard">
    <main class="main-content">
        <h2>Add New User</h2>

        <?php if($error) echo "<div class='error-msg'>$error</div>"; ?>
        <?php if($success) echo "<div class='success-msg'>$success</div>"; ?>

        <form method="POST">
            <div class="input-group">
                <label>Name</label>
                <input type="text" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="input-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="">-- Select Role --</option>
                    <option value="user" <?php if(($_POST['role'] ?? '')==='user') echo 'selected'; ?>>User</option>
                    <option value="staff" <?php if(($_POST['role'] ?? '')==='staff') echo 'selected'; ?>>Staff</option>
                    <option value="admin" <?php if(($_POST['role'] ?? '')==='admin') echo 'selected'; ?>>Admin</option>
                </select>
            </div>
            <div class="input-group">
                <label>Department</label>
                <select name="department_id">
                    <option value="">-- None --</option>
                    <?php while($d = mysqli_fetch_assoc($departments)) { ?>
                        <option value="<?php echo $d['id']; ?>" <?php if(($_POST['department_id'] ?? '') == $d['id']) echo 'selected'; ?>>
                            <?php echo $d['department_name']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <button type="submit" class="btn primary">Add User</button>
        </form>
    </main>
</div>
</body>
</html>