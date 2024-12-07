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

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["csvFile"])) {
    $file = $_FILES["csvFile"]["tmp_name"];

    if (($handle = fopen($file, "r")) !== FALSE) {
        $rowIndex = 0;

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if ($rowIndex == 0) {
                $rowIndex++;
                continue;
            }

            // Assuming CSV format: last_name, first_name, gender, department
            $last_name = $data[0];
            $first_name = $data[1];
            $gender = $data[2];
            $department = $data[3];

            // Insert the data into the Members table
            $stmt = $conn->prepare("INSERT INTO Members (last_name, first_name, gender, department) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $last_name, $first_name, $gender, $department);
            $stmt->execute();
        }

        fclose($handle);
        $message = "CSV file uploaded successfully!";
    } else {
        $message = "Error reading the file.";
    }
}

$conn->close();
header("Location: display_members.php");
?>
