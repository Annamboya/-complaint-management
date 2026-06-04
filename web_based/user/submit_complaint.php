<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../includes/auth.php";
include "../db.php";

// Only allow users
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit();
}

// ================= FETCH CATEGORIES =================
$categories = $conn->query("SELECT * FROM categories ORDER BY category_name ASC");

// ================= AJAX: LOAD TYPES =================
if(isset($_GET['category_id'])){
    $cat_id = intval($_GET['category_id']);

    $stmt = $conn->prepare("SELECT id, type_name FROM complaint_types WHERE category_id=?");
    $stmt->bind_param("i", $cat_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while($row = $result->fetch_assoc()){
        $data[] = $row;
    }

    echo json_encode($data);
    exit();
}

// ================= HANDLE SUBMISSION =================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax'])) {

    $complaint_type_id = intval($_POST['complaint_type_id']);
    $description = trim($_POST['description']);
    $user_id = $_SESSION['user_id'];

    if (!$complaint_type_id || empty($description)) {
        echo json_encode(['status'=>'error','message'=>'Please fill all required fields.']);
        exit();
    }

    // Get type details
    $stmt = $conn->prepare("SELECT category_id, department_id, type_name FROM complaint_types WHERE id=?");
    $stmt->bind_param("i", $complaint_type_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if(!$result){
        echo json_encode(['status'=>'error','message'=>'Invalid complaint type.']);
        exit();
    }

    $category_id = $result['category_id'];
    $department_id = $result['department_id'];
    $title = $result['type_name'];

    // ================= AUTO ASSIGN STAFF =================
    $staff_query = $conn->prepare("
        SELECT id, total_assigned 
        FROM users 
        WHERE role='staff' AND department_id=? AND status='active'
        ORDER BY total_assigned ASC 
        LIMIT 1
    ");
    $staff_query->bind_param("i", $department_id);
    $staff_query->execute();
    $staff_result = $staff_query->get_result()->fetch_assoc();

    $assigned_to = NULL;
    if($staff_result){
        $assigned_to = $staff_result['id'];
    }

    // ================= INSERT COMPLAINT =================
    $stmt = $conn->prepare("
        INSERT INTO complaints 
        (user_id, category_id, complaint_type_id, department_id, assigned_to, title, description) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iiiiiss", 
        $user_id, 
        $category_id, 
        $complaint_type_id, 
        $department_id, 
        $assigned_to, 
        $title, 
        $description
    );

    if ($stmt->execute()) {
        $complaint_id = $stmt->insert_id;

        // Update workload for staff
        if($assigned_to){
            $conn->query("
                UPDATE users 
                SET total_assigned = total_assigned + 1 
                WHERE id = $assigned_to
            ");
        }

        // Log submission
        $log = $conn->prepare("INSERT INTO complaint_logs (complaint_id, action, performed_by) VALUES (?, ?, ?)");
        $msg = $assigned_to ? "Auto-assigned to staff ID $assigned_to" : "No staff available";
        $log->bind_param("isi", $complaint_id, $msg, $user_id);
        $log->execute();

        echo json_encode(['status'=>'success','message'=>'Complaint submitted & assigned!']);
    } else {
        echo json_encode(['status'=>'error','message'=>'Database error.']);
    }

    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Complaint</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>

<div class="dashboard">
    
    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2>User Panel</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="submit_complaint.php" class="active">Submit Complaint</a>
        <a href="my_complaints.php">My Complaints</a>
        <a href="../logout.php">Logout</a>
    </div>

    <!-- MAIN -->
    <div class="main-content">
        <h1>Submit Complaint</h1>

        <div id="notification"></div>

        <form id="complaintForm">

            <!-- CATEGORY -->
            <div class="input-group">
                <label>Category</label>
                <select id="category" required>
                    <option value="">Select Category</option>
                    <?php while($cat = $categories->fetch_assoc()): ?>
                        <option value="<?php echo $cat['id']; ?>">
                            <?php echo htmlspecialchars($cat['category_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- TYPE -->
            <div class="input-group">
                <label>Complaint Type</label>
                <select name="complaint_type_id" id="complaint_type" required>
                    <option value="">Select Complaint Type</option>
                </select>
            </div>

            <!-- DESCRIPTION -->
            <div class="input-group">
                <label>Description</label>
                <textarea name="description" rows="5" required 
                placeholder="Provide details (e.g. transaction code, course code, date)"></textarea>
            </div>

            <button type="submit" class="btn primary">Submit Complaint</button>
        </form>
    </div>
</div>

<script>
$(document).ready(function(){

    // Load complaint types dynamically
    $('#category').change(function(){
        let category_id = $(this).val();

        if(!category_id){
            $('#complaint_type').html('<option>Select Complaint Type</option>');
            return;
        }

        $('#complaint_type').html('<option>Loading...</option>');

        $.get('submit_complaint.php', {category_id: category_id}, function(data){
            let types = JSON.parse(data);
            let options = '<option value="">Select Complaint Type</option>';

            types.forEach(function(type){
                options += `<option value="${type.id}">${type.type_name}</option>`;
            });

            $('#complaint_type').html(options);
        });
    });

    // Submit form via AJAX
    $('#complaintForm').submit(function(e){
        e.preventDefault();

        let formData = $(this).serialize() + "&ajax=1";

        $.post('submit_complaint.php', formData, function(response){
            let data = JSON.parse(response);

            if(data.status === 'success'){
                $('#notification').html('<div class="success-msg">'+data.message+'</div>');
                $('#complaintForm')[0].reset();
                $('#complaint_type').html('<option>Select Complaint Type</option>');
            } else {
                $('#notification').html('<div class="error-msg">'+data.message+'</div>');
            }
        });
    });

});
</script>

</body>
</html>