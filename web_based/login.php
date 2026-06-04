<?php
session_start();
include "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND status='active'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['department_id'] = $user['department_id'];

            if ($user['role'] === "admin") {
                header("Location: admin/dashboard.php");
            } elseif ($user['role'] === "staff") {
                header("Location: staff/dashboard.php");
            } else {
                header("Location: user/dashboard.php");
            }
            exit();

        } else {
            $error = "Invalid email or password.";
        }

    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - WCMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="login-wrapper">

    <div class="login-left">
        <h1>WCMS</h1>
        <p>
            Secure institutional complaint management platform designed
            to improve transparency, accountability and operational efficiency.
        </p>
    </div>

    <div class="login-right">

        <div class="login-card">

            <h2>Sign In</h2>

            <?php if(!empty($error)) { ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php } ?>

            <form method="POST">

                <div class="input-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required>
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <button type="submit" class="btn-login">Login</button>

            </form>

            <div class="login-footer">
                Don't have an account?
                <a href="register.php">Register</a>
            </div>
            <div class="login-footer">
                <a href="forgot.php">Forgot Password?</a>
            </div>
        </div>

    </div>

</div>

</body>
</html>