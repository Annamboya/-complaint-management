<?php
include "../includes/auth.php";
include "../db.php";

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if(!isset($_GET['id'])) die("User ID missing");
$id = intval($_GET['id']);

if($id != $_SESSION['user_id']){
    // Log deletion
    $conn->query("INSERT INTO complaint_logs (complaint_id, action, performed_by)
                  VALUES (0,'Deleted user ID $id by admin','".$_SESSION['user_id']."')");
    mysqli_query($conn,"DELETE FROM users WHERE id=$id");
}
header("Location: users.php");
exit();