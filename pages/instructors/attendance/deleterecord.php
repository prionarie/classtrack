<?php
include "../../../assets/db/dbcon.php";
session_start();

if (!isset($_SESSION['employee_id'])) {
    header("Location: ../welcome/login.php");
    exit;
  }

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $record_id = $_POST['record_id'] ?? '';

    if (empty($record_id)) {
        die("Error: Missing record ID.");
    }

    $stmt = $con->prepare("DELETE FROM attendance_records WHERE record_id = ?");
    $stmt->bind_param("i", $record_id);

    if ($stmt->execute()) {
        header("Location: ../lists/attendancelist.php?delete=success");
        exit();
    } else {
        echo "Error deleting record: " . $stmt->error;
    }

    $stmt->close();
}
else {
    echo "Invalid request method.";
}
?>