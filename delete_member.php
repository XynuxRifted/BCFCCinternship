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

// Delete member and reassign IDs
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Delete the member with the specified ID
    $deleteSql = "DELETE FROM Members WHERE id = ?";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Reassign the IDs of the remaining members
    $reassignSql = "SET @count = 0; 
                    UPDATE Members SET id = @count := (@count + 1)";
    $conn->query($reassignSql);

    // Reset auto-increment to follow the new IDs starting from 1
    $resetSql = "ALTER TABLE Members AUTO_INCREMENT = 1";
    $conn->query($resetSql);

    // Redirect back to members list
    header("Location: display_members.php");
    exit;
}

$conn->close();
?>
