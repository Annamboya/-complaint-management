<?php
include "../db.php";
include "../includes/auth.php";

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_POST['email'])) {
    $email = $_POST['email'];
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user) {
        $token = bin2hex(random_bytes(20));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $update = $conn->prepare("UPDATE users SET reset_token=?, reset_expires=? WHERE id=?");
        $update->bind_param("ssi", $token, $expires, $user['id']);
        $update->execute();

        // In production, send this link via email
        $resetLink = "https://yourdomain.com/admin/reset_password.php?token=$token";
        echo "Password reset link: <a href='$resetLink'>$resetLink</a>";
    } else {
        echo "User not found";
    }
}
?>

<form method="POST">
    <input type="email" name="email" placeholder="User email" required>
    <button type="submit">Send Reset Link</button>
</form>