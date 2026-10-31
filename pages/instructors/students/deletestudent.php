<?php
include "../../../assets/db/dbcon.php";
session_start();

if (!isset($_SESSION['employee_id'])) {
  header("Location: ../welcome/login.php");
  exit;
}

if (!isset($con)) {
    echo "Database connection error.";
    exit();
}

if (isset($_POST['student_number'], $_POST['class_id'])) {
    $student_number = $_POST['student_number'];
    $class_id = $_POST['class_id'];

    $stmt = $con->prepare("DELETE FROM student_classes WHERE student_number = ? AND class_id = ?");
    $stmt->bind_param("ii", $student_number, $class_id);

    if ($stmt->execute()) {
        header("Location: ../lists/studentlist.php?class_id=" . urlencode($class_id) . "&delete=success");
        exit();
    } else {
        echo "Error deleting student: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Error: Missing student number or class ID.";
}

$con->close();
?>