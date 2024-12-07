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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $gender = $_POST['gender'];
    $department = $_POST['department'];

    // Prepare and bind the statement
    $stmt = $conn->prepare("INSERT INTO Members (last_name, first_name, gender, department) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $last_name, $first_name, $gender, $department);

    // Execute the query
    if ($stmt->execute()) {
        // Redirect back to index if successful
        header("Location: index.html");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}

// Close the connection
$conn->close();
?>
