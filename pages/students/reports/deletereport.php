<?php
include "../../../assets/db/dbcon.php";
session_start();

if (!isset($_SESSION['student_number'])) {
    header("Location: ../welcome/login.php");
    exit;
}

$student_number = $_SESSION['student_number'];

if (!isset($_POST['report_id'])) {
    die("No report ID.");
}

$report_id = $_POST['report_id'];

$stmt = $con->prepare("SELECT file_upload FROM absence_reports WHERE report_id = ? AND student_number = ?");
$stmt->bind_param("ii", $report_id, $student_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Report not found.");
}

$report = $result->fetch_assoc();
$file_path = $report['file_upload'];

$att_stmt = $con->prepare("SELECT attendance_id FROM student_attendance WHERE report_id = ?");
$att_stmt->bind_param("i", $report_id);
$att_stmt->execute();
$att_result = $att_stmt->get_result();

$attendance_id = null;
if ($att_result->num_rows > 0) {
    $att_row = $att_result->fetch_assoc();
    $attendance_id = $att_row['attendance_id'];
}

$stmt = $con->prepare("DELETE FROM absence_reports WHERE report_id = ? AND student_number = ?");
$stmt->bind_param("ii", $report_id, $student_number);

if ($stmt->execute()) {
    if (!empty($file_path) && file_exists($file_path)) {
        unlink($file_path);
    }

    if ($attendance_id !== null) {
        $update_stmt = $con->prepare("UPDATE student_attendance SET report_id = NULL WHERE attendance_id = ?");
        $update_stmt->bind_param("i", $attendance_id);
        $update_stmt->execute();
    }

    header("Location: ../lists/reportlist.php");
    exit;
} else {
    die("Failed to delete report.");
}
?>