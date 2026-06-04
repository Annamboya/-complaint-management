<?php
include "../includes/auth.php";
include "../db.php";

if ($_SESSION['role'] !== 'admin') header("Location: ../login.php");

$id = $_GET['id'];

// Audit log before deletion
$admin_id = $_SESSION['user_id'];
mysqli_query($conn,"INSERT INTO complaint_logs (complaint_id,action,performed_by) VALUES ($id,'Deleted complaint','$admin_id')");

// Delete complaint
mysqli_query($conn,"DELETE FROM complaints WHERE id=$id");
header("Location: view_complaints.php");
exit();
?>