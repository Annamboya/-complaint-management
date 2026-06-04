<?php
session_start();
include "db.php";

$error = "";
$success = "";
$password_error = "";

function validate_password($password, $name, $email) {
    if (strlen($password) < 8) {
        return "Password must be at least 8 characters long.";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return "Password must include at least one uppercase letter.";
    }
    if (!preg_match('/[a-z]/', $password)) {
        return "Password must include at least one lowercase letter.";
    }
    if (!preg_match('/[0-9]/', $password)) {
        return "Password must include at least one number.";
    }
    if (!preg_match('/[\W_]/', $password)) {
        return "Password must include at least one special character.";
    }
    if (strcasecmp($password, $name) === 0 || strcasecmp($password, $email) === 0) {
        return "Password cannot be the same as your name or email.";
    }
    return "";
}

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Password security validation
    $password_error = validate_password($password, $name, $email);
    $error = $password_error;

    if (empty($error)) {
        if ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "Email is already registered.";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert user into database
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'user', 'active')");
                $stmt->bind_param("sss", $name, $email, $hashed_password);

                if ($stmt->execute()) {
                    $success = "Registration successful! You can now <a href='login.php'>login</a>.";
                } else {
                    $error = "Something went wrong. Please try again.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - WCMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="login-wrapper">

    <!-- Left section -->
    <div class="login-left">
        <h1>WCMS</h1>
        <p>Secure institutional complaint management platform designed
            to improve transparency, accountability, and operational efficiency.
        </p>
    </div>

    <!-- Right section: register form -->
    <div class="login-right">

        <div class="login-card">
            <h2>Create Account</h2>

            <?php if(!empty($error)): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if(!empty($success)): ?>
                <div class="success-msg"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="input-group">
                    <label>Full Name</label>
                    <input type="text" name="name" placeholder="Enter your full name" required>
                </div>

                <div class="input-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <span id="passwordError" class="input-error <?php echo !empty($password_error) ? '' : 'hidden'; ?>">
                        <?php echo htmlspecialchars($password_error); ?>
                    </span>
                </div>

                <div class="input-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" placeholder="Re-enter your password" required>
                </div>

                <button type="submit" class="btn-login">Register</button>
            </form>

            <div class="login-footer">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>

    </div>

</div>

<script>
(function() {
    var passwordInput = document.getElementById('password');
    var nameInput = document.querySelector('input[name="name"]');
    var emailInput = document.querySelector('input[name="email"]');
    var passwordError = document.getElementById('passwordError');
    var form = document.querySelector('form');

    function validatePassword(password, name, email) {
        if (password.length < 8) {
            return 'Password must be at least 8 characters long.';
        }
        if (!/[A-Z]/.test(password)) {
            return 'Password must include at least one uppercase letter.';
        }
        if (!/[a-z]/.test(password)) {
            return 'Password must include at least one lowercase letter.';
        }
        if (!/[0-9]/.test(password)) {
            return 'Password must include at least one number.';
        }
        if (!/[\W_]/.test(password)) {
            return 'Password must include at least one special character.';
        }
        if (name && password.toLowerCase() === name.toLowerCase()) {
            return 'Password cannot be the same as your name or email.';
        }
        if (email && password.toLowerCase() === email.toLowerCase()) {
            return 'Password cannot be the same as your name or email.';
        }
        return '';
    }

    function updatePasswordError() {
        var error = validatePassword(passwordInput.value, nameInput.value, emailInput.value);
        if (error) {
            passwordError.textContent = error;
            passwordError.classList.remove('hidden');
        } else {
            passwordError.textContent = '';
            passwordError.classList.add('hidden');
        }
        return error;
    }

    passwordInput.addEventListener('input', updatePasswordError);
    nameInput.addEventListener('input', updatePasswordError);
    emailInput.addEventListener('input', updatePasswordError);

    form.addEventListener('submit', function(e) {
        if (updatePasswordError()) {
            e.preventDefault();
            passwordInput.focus();
        }
    });
})();
</script>

</body>
</html>