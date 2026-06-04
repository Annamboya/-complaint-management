<?php
include "../includes/auth.php";
include "../db.php";
include "../includes/header.php";

// get complaints that are not assigned
$complaints = mysqli_query($conn,
    "SELECT * FROM complaints WHERE staff_id IS NULL"
);

// get staff members
$staff = mysqli_query($conn,
    "SELECT * FROM users WHERE role='staff'"
);

// when admin clicks assign
if (isset($_POST['assign'])) {
    $complaint_id = $_POST['complaint_id'];
    $staff_id = $_POST['staff_id'];

    mysqli_query($conn,
        "UPDATE complaints 
         SET staff_id='$staff_id', status='Assigned'
         WHERE id='$complaint_id'"
    );

    echo "<p style='color:green;'>Complaint assigned successfully!</p>";
}
?>

<h3>Assign Complaint</h3>

<form method="POST">
    Complaint:<br>
    <select name="complaint_id" required>
        <option value="">-- Select Complaint --</option>
        <?php while ($c = mysqli_fetch_assoc($complaints)) { ?>
            <option value="<?php echo $c['id']; ?>">
                <?php echo $c['reference_no']; ?> - <?php echo $c['title']; ?>
            </option>
        <?php } ?>
    </select>
    <br><br>

    Staff Member:<br>
    <select name="staff_id" required>
        <option value="">-- Select Staff --</option>
        <?php while ($s = mysqli_fetch_assoc($staff)) { ?>
            <option value="<?php echo $s['id']; ?>">
                <?php echo $s['name']; ?>
            </option>
        <?php } ?>
    </select>
    <br><br>

    <button name="assign">Assign Complaint</button>
</form>

<br>
<a href="dashboard.php">Back to Dashboard</a>

<?php include "../includes/footer.php"; ?>