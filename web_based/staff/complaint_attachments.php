<?php
include "../includes/auth.php";
include "../db.php";

if ($_SESSION['role'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}

$staff_id = $_SESSION['user_id'];

// Validate complaint ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) die("Invalid Complaint ID.");
$complaint_id = intval($_GET['id']);

// Check if complaint is assigned to this staff
$stmt = $conn->prepare("SELECT * FROM complaints WHERE id=? AND assigned_to=?");
$stmt->bind_param("ii",$complaint_id,$staff_id);
$stmt->execute();
$complaint = $stmt->get_result()->fetch_assoc();
if(!$complaint) die("Complaint not found or not assigned to you.");

// Handle file upload
$error = $success = "";
if(isset($_POST['upload']) && isset($_FILES['attachment'])){
    $file = $_FILES['attachment'];
    $target_dir = "../uploads/";
    if(!is_dir($target_dir)) mkdir($target_dir,0777,true);
    
    $file_name = basename($file['name']);
    $target_file = $target_dir . time() . "_" . $file_name;
    
    if(move_uploaded_file($file['tmp_name'],$target_file)){
        $stmt2 = $conn->prepare("INSERT INTO complaint_attachments (complaint_id, file_name, file_path) VALUES (?,?,?)");
        $stmt2->bind_param("iss",$complaint_id,$file_name,$target_file);
        if($stmt2->execute()){
            $success = "File uploaded successfully.";
        } else { $error = "DB error: ".$conn->error; }
    } else { $error = "Failed to upload file."; }
}

// Fetch attachments
$attachments = mysqli_query($conn, "SELECT * FROM complaint_attachments WHERE complaint_id=$complaint_id ORDER BY uploaded_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Complaint Attachments #<?php echo $complaint_id;?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="form-wrapper">
<h2>Attachments for Complaint #<?php echo $complaint_id;?></h2>
<?php if($error) echo "<div class='error-msg'>$error</div>"; ?>
<?php if($success) echo "<div class='success-msg'>$success</div>"; ?>

<form method="POST" enctype="multipart/form-data">
    <div class="input-group">
        <label>Upload File</label>
        <input type="file" name="attachment" required>
    </div>
    <button type="submit" name="upload" class="btn primary">Upload</button>
</form>

<h3>Existing Files</h3>
<ul>
<?php while($a = mysqli_fetch_assoc($attachments)) { ?>
    <li>
        <a href="<?php echo $a['file_path'];?>" target="_blank"><?php echo htmlspecialchars($a['file_name']);?></a>
        <small>(<?php echo $a['uploaded_at']; ?>)</small>
    </li>
<?php } ?>
</ul>
<a href="view_complaints.php" class="btn">Back to Complaints</a>
</div>
</body>
</html>