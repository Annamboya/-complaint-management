<?php
include "db.php";

$token = $_GET['token'];

$stmt = $conn->prepare("SELECT * FROM users WHERE reset_token=? AND reset_expires > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password=?, reset_token=NULL, reset_expires=NULL WHERE reset_token=?");
        $stmt->bind_param("ss", $newPassword, $token);
        $stmt->execute();

        echo "Password updated!";
    }
} else {
    echo "Invalid or expired token.";
}
?>

<form method="POST">
    <input type="password" name="password" placeholder="New password" required>
    <button type="submit">Update Password</button>
</form>