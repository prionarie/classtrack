<?php
include "../../../assets/db/dbcon.php";
session_start();

if (!isset($_SESSION['employee_id'])) {
    header("Location: ../welcome/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $report_id = $_POST['report_id'];
    $action = $_POST['action'];
    $feedback = $_POST['feedback'] ?? null;

    if (in_array($action, ['Validated', 'Rejected'])) {
        $stmt = $con->prepare("UPDATE absence_reports SET remarks = ?, feedback = ? WHERE report_id = ?");
        $stmt->bind_param("ssi", $action, $feedback, $report_id);

        if ($stmt->execute()) {
            header("Location: ../lists/reportlist.php");
            exit;
        } else {
            echo "Failed to update report.";
        }
    } else {
        echo "Invalid action.";
    }
} else {
    echo "Invalid request.";
}