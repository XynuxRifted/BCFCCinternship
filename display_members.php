<?php
// Database connection variables
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

// Handle bulk delete form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_selected'])) {
    $selectedIds = $_POST['member_ids'] ?? [];
    if (!empty($selectedIds)) {
        $ids = implode(",", array_map('intval', $selectedIds));
        $deleteSql = "DELETE FROM Members WHERE id IN ($ids)";
        $conn->query($deleteSql);

        // Reset auto-increment if all members are deleted
        $resetSql = "ALTER TABLE Members AUTO_INCREMENT = 1";
        $conn->query($resetSql);

        // Redirect to refresh the page
        header("Location: display_members.php");
        exit();
    }
}

// Initialize variables for search query and count
$searchQuery = "";
$memberCount = 0;

if (isset($_POST['search'])) {
    $searchQuery = $_POST['search'];
    // Fetch members based on search query
    $sql = "SELECT id, last_name, first_name, gender, department FROM Members 
            WHERE first_name LIKE ? OR last_name LIKE ? OR gender LIKE ? OR department LIKE ?";
    $stmt = $conn->prepare($sql);
    $searchPattern = "%$searchQuery%";
    $stmt->bind_param("ssss", $searchPattern, $searchPattern, $searchPattern, $searchPattern);
    $stmt->execute();
    $result = $stmt->get_result();

    // Get the count of the matching members
    $memberCount = $result->num_rows;
} else {
    // If no search, display all members and get the count
    $sql = "SELECT id, last_name, first_name, gender, department FROM Members";
    $result = $conn->query($sql);
    $memberCount = $result->num_rows;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Members</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- jQuery (for AJAX) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
    <!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.html">Member Management</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="index.html">Add Members</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="display_members.php">View Members</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h1 class="text-center">Members List</h1>

    <!-- Search Bar -->
    <div class="mb-4">
        <input type="text" id="search" class="form-control" placeholder="Search by Name, Gender, or Department" onkeyup="searchMembers()">
    </div>

    <!-- Displaying the count of members -->
    <h3 id="memberCount" class="text-center">Total Members: <?php echo $memberCount; ?></h3>

    <!-- Form for bulk delete -->
    <form method="POST">
        <table class="table table-bordered table-striped mt-4" id="membersTable">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Department</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Concatenate last name and first name
                        $full_name = $row['last_name'] . ', ' . $row['first_name'];
                        echo "<tr>";
                        echo "<td><input type='checkbox' name='member_ids[]' value='" . $row['id'] . "'></td>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . $full_name . "</td>";
                        echo "<td>" . $row['gender'] . "</td>";
                        echo "<td>" . $row['department'] . "</td>";
                        echo "<td>
                            <a href='edit_member.php?id=" . $row['id'] . "' class='btn btn-warning btn-sm'>Edit</a>
                            <a href='delete_member.php?id=" . $row['id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this member?\")'>Delete</a>
                        </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center'>No members found</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <!-- Bulk delete button -->
        <div class="text-center">
            <button type="submit" name="delete_selected" class="btn btn-danger">Delete Selected Members</button>
        </div>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Select or deselect all checkboxes
document.getElementById('selectAll').addEventListener('click', function() {
    let checkboxes = document.querySelectorAll("input[name='member_ids[]']");
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
});

// Function to handle search as the user types
function searchMembers() {
    var searchQuery = document.getElementById('search').value;

    $.ajax({
        url: 'display_members.php', // The same page to process the request
        type: 'POST',
        data: { search: searchQuery },
        success: function(data) {
            // Only update the table body and count, not the entire page
            $('#membersTable tbody').html($(data).find('tbody').html());
            $('#memberCount').html("Total Members: " + $(data).find('#memberCount').html());
        }
    });
}
</script>

</body>
</html>

<?php
$conn->close();
?>
