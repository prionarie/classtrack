<?php
include "../../../assets/db/dbcon.php";
session_start();

if (!isset($_SESSION['student_number'])) {
    header("Location: ../welcome/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_number = $_POST['student_number'];
    $class_id = $_POST['class_id'];

    $stmt = $con->prepare("DELETE FROM student_classes WHERE student_number = ? AND class_id = ?");
    $stmt->bind_param("ii", $student_number, $class_id);

    if ($stmt->execute()) {
        header("Location: ../lists/classlist.php?leave=success");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $con->close();
}
?>