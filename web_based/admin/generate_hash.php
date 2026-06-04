<?php
// Change password here
$password = "admin123";

// Generate hash
$hash = password_hash($password, PASSWORD_DEFAULT);

echo $hash;
?>