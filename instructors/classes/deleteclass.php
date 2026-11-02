<?php
include "../../config/dbcon.php";
session_start();

if (!isset($_SESSION['employee_id'])) {
    header("Location: ../welcome/login.php");
    exit;
  }

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['class_id'])) {
    $class_id = $_POST['class_id'];

    $stmt = $con->prepare("DELETE FROM classes WHERE class_id = ?");
    $stmt->bind_param("i", $class_id);

    if ($stmt->execute()) {
        header("Location: ../lists/classlist.php?delete=success");
        exit();
    } else {
        echo "Error deleting class: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid request method.";
}

$con->close();
?>