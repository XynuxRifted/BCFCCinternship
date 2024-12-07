<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "loan_db";

// Connect to the database
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get member details
$id = $_GET['id'];
$sql = "SELECT * FROM Members WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();

// Update member details
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $gender = $_POST['gender'];
    $department = $_POST['department'];

    $updateSql = "UPDATE Members SET last_name = ?, first_name = ?, gender = ?, department = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("ssssi", $last_name, $first_name, $gender, $department, $id);
    $updateStmt->execute();

    // Redirect back to members list
    header("Location: display_members.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Member</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Edit Member</h1>
    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label for="last_name" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $member['last_name']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="first_name" class="form-label">First Name</label>
            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $member['first_name']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="gender" class="form-label">Gender</label>
            <select class="form-control" id="gender" name="gender" required>
                <option value="Male" <?php echo ($member['gender'] == 'MALE') ? 'selected' : ''; ?>>MALE</option>
                <option value="Female" <?php echo ($member['gender'] == 'FEMALE') ? 'selected' : ''; ?>>FEMALE</option>
                <option value="Other" <?php echo ($member['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="department" class="form-label">Department</label>
            <input type="text" class="form-control" id="department" name="department" value="<?php echo $member['department']; ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Member</button>
        <a href="display_members.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>
