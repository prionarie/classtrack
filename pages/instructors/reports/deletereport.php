<?php
include "../../../assets/db/dbcon.php";
session_start();

if (!isset($_SESSION['employee_id'])) {
    header("Location: ../welcome/login.php");
    exit;
}

if (!isset($_POST['report_id'])) {
    die("No report ID.");
}

$report_id = $_POST['report_id'];

$stmt = $con->prepare("SELECT file_upload FROM absence_reports WHERE report_id = ?");
$stmt->bind_param("i", $report_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Report not found.");
}

$report = $result->fetch_assoc();
$file_path = $report['file_upload'];

$update_stmt = $con->prepare("UPDATE student_attendance SET report_id = NULL WHERE report_id = ?");
$update_stmt->bind_param("i", $report_id);
$update_stmt->execute();

$delete_stmt = $con->prepare("DELETE FROM absence_reports WHERE report_id = ?");
$delete_stmt->bind_param("i", $report_id);

if ($delete_stmt->execute()) {
    if (!empty($file_path) && file_exists($file_path)) {
        unlink($file_path);
    }

    header("Location: ../lists/reportlist.php");
    exit;
} else {
    die("Failed to delete report.");
}
?>
