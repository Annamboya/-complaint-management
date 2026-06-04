<?php
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    $token = bin2hex(random_bytes(50));
    $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

    $stmt = $conn->prepare("UPDATE users SET reset_token=?, reset_expires=? WHERE email=?");
    $stmt->bind_param("sss", $token, $expires, $email);
    $stmt->execute();

    echo "Reset link: http://localhost/reset.php?token=$token";
}
?>

<form method="POST">
    <input type="email" name="email" placeholder="Enter email" required>
    <button type="submit">Reset Password</button>
</form>