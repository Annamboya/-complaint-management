<?php
include "../includes/auth.php";
include "../db.php";

// Only users can access
if ($_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit();
}

// Get complaint ID
$complaint_id = intval($_GET['id'] ?? 0);

// Fetch the complaint to ensure it belongs to the user
$stmt = $conn->prepare("SELECT * FROM complaints WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $complaint_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$complaint = $result->fetch_assoc();

if (!$complaint) {
    die("Complaint not found or access denied.");
}

// Fetch categories and departments for dropdowns
$categories = $conn->query("SELECT * FROM categories ORDER BY category_name ASC");
$departments = $conn->query("SELECT * FROM departments ORDER BY department_name ASC");

// Handle AJAX update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category_id = intval($_POST['category_id']);
    $department_id = intval($_POST['department_id']);

    if (empty($title) || empty($description) || !$category_id || !$department_id) {
        echo json_encode(['status'=>'error','message'=>'All fields are required.']);
        exit();
    }

    $update_stmt = $conn->prepare("UPDATE complaints SET title=?, description=?, category_id=?, department_id=? WHERE id=? AND user_id=?");
    $update_stmt->bind_param("siiiii", $title, $description, $category_id, $department_id, $complaint_id, $_SESSION['user_id']);

    if ($update_stmt->execute()) {
        // Log the edit
        $log_stmt = $conn->prepare("INSERT INTO complaint_logs (complaint_id, action, performed_by) VALUES (?, ?, ?)");
        $action_msg = "Complaint edited by user";
        $log_stmt->bind_param("isi", $complaint_id, $action_msg, $_SESSION['user_id']);
        $log_stmt->execute();

        echo json_encode(['status'=>'success','message'=>'Complaint updated successfully!']);
    } else {
        echo json_encode(['status'=>'error','message'=>'Failed to update complaint.']);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Complaint</title>
<link rel="stylesheet" href="../assets/css/style.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="dashboard">
    <div class="sidebar">
        <h2>User Panel</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="submit_complaint.php">Submit Complaint</a>
        <a href="my_complaints.php">My Complaints</a>
        <a href="../logout.php">Logout</a>
    </div>

    <div class="main-content">
        <h1>Edit Complaint</h1>
        <div id="notification"></div>

        <form id="editForm">
            <div class="input-group">
                <label>Category</label>
                <select name="category_id" required>
                    <option value="">Select Category</option>
                    <?php while($cat = $categories->fetch_assoc()): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id']==$complaint['category_id'])?'selected':''; ?>>
                            <?php echo htmlspecialchars($cat['category_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="input-group">
                <label>Department</label>
                <select name="department_id" required>
                    <option value="">Select Department</option>
                    <?php while($dept = $departments->fetch_assoc()): ?>
                        <option value="<?php echo $dept['id']; ?>" <?php echo ($dept['id']==$complaint['department_id'])?'selected':''; ?>>
                            <?php echo htmlspecialchars($dept['department_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="input-group">
                <label>Title</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($complaint['title']); ?>" required>
            </div>

            <div class="input-group">
                <label>Description</label>
                <textarea name="description" rows="6" required><?php echo htmlspecialchars($complaint['description']); ?></textarea>
            </div>

            <button type="submit" class="btn primary">Update Complaint</button>
        </form>
    </div>
</div>

<script>
$(document).ready(function(){
    $('#editForm').submit(function(e){
        e.preventDefault();
        let formData = $(this).serialize() + "&ajax=1";

        $.post('edit_complaint.php?id=<?php echo $complaint_id; ?>', formData, function(response){
            let data = JSON.parse(response);
            let notification = $('#notification');

            if(data.status === 'success'){
                notification.html('<div class="success-msg">'+data.message+'</div>');
            } else {
                notification.html('<div class="error-msg">'+data.message+'</div>');
            }
        });
    });
});
</script>

</body>
</html>