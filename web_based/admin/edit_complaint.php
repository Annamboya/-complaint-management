<?php
include "../includes/auth.php";
include "../db.php";

if ($_SESSION['role'] !== 'admin') header("Location: ../login.php");

$id = $_GET['id'];
$complaint = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM complaints WHERE id=$id"));
$departments = mysqli_query($conn,"SELECT * FROM departments");
$categories = mysqli_query($conn,"SELECT * FROM categories");
$error = $success = "";

if($_SERVER['REQUEST_METHOD']=="POST"){
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];
    $priority = $_POST['priority'];
    $department_id = $_POST['department_id'];
    $category_id = $_POST['category_id'];

    $stmt = $conn->prepare("UPDATE complaints SET title=?, description=?, status=?, priority=?, department_id=?, category_id=? WHERE id=?");
    $stmt->bind_param("sssiiii",$title,$description,$status,$priority,$department_id,$category_id,$id);

    if($stmt->execute()){
        $success = "Complaint updated successfully.";
        $admin_id = $_SESSION['user_id'];
        mysqli_query($conn,"INSERT INTO complaint_logs (complaint_id,action,performed_by) VALUES ($id,'Edited complaint details','$admin_id')");
    } else {
        $error = "Error: ".$conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Complaint - WCMS</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="form-wrapper">
<h2>Edit Complaint #<?php echo $id;?></h2>
<?php if($error) echo "<div class='error-msg'>$error</div>"; ?>
<?php if($success) echo "<div class='success-msg'>$success</div>"; ?>

<form method="POST">
<div class="input-group"><label>Title</label><input type="text" name="title" value="<?php echo htmlspecialchars($complaint['title']);?>" required></div>
<div class="input-group"><label>Description</label><textarea name="description" required><?php echo htmlspecialchars($complaint['description']);?></textarea></div>
<div class="input-group"><label>Department</label>
<select name="department_id">
<?php while($d = mysqli_fetch_assoc($departments)){ ?>
<option value="<?php echo $d['id'];?>" <?php if($complaint['department_id']==$d['id']) echo "selected";?>><?php echo $d['department_name'];?></option>
<?php } ?>
</select></div>
<div class="input-group"><label>Category</label>
<select name="category_id">
<?php while($c = mysqli_fetch_assoc($categories)){ ?>
<option value="<?php echo $c['id'];?>" <?php if($complaint['category_id']==$c['id']) echo "selected";?>><?php echo $c['category_name'];?></option>
<?php } ?>
</select></div>
<div class="input-group"><label>Status</label>
<select name="status">
<option value="Pending" <?php if($complaint['status']=='Pending') echo "selected";?>>Pending</option>
<option value="In Progress" <?php if($complaint['status']=='In Progress') echo "selected";?>>In Progress</option>
<option value="Resolved" <?php if($complaint['status']=='Resolved') echo "selected";?>>Resolved</option>
<option value="Rejected" <?php if($complaint['status']=='Rejected') echo "selected";?>>Rejected</option>
</select></div>
<div class="input-group"><label>Priority</label>
<select name="priority">
<option value="Low" <?php if($complaint['priority']=='Low') echo "selected";?>>Low</option>
<option value="Medium" <?php if($complaint['priority']=='Medium') echo "selected";?>>Medium</option>
<option value="High" <?php if($complaint['priority']=='High') echo "selected";?>>High</option>
<option value="Critical" <?php if($complaint['priority']=='Critical') echo "selected";?>>Critical</option>
</select></div>

<button type="submit" class="btn primary">Update Complaint</button>
</form>
<a href="view_complaints.php" class="btn">Back to Complaints</a>
</div>
</body>
</html>