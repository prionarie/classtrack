<?php
include '../../../assets/db/dbcon.php';
session_start();

if (!isset($_SESSION['employee_id'])) {
    header("Location: ../welcome/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $record_id = $_POST['record_id'];

    $query_class_id = "SELECT class_id FROM attendance_records WHERE record_id = ?";
    $stmt = $con->prepare($query_class_id);
    $stmt->bind_param("i", $record_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo "Attendance record ID not found.";
        exit;
    }
    $row = $result->fetch_assoc();
    $class_id = $row['class_id'];

    foreach ($_POST['status'] as $student_number => $status) {
        $query_report = "SELECT report_id FROM absence_reports WHERE student_number = ? AND record_id = ?";
        $stmt = $con->prepare($query_report);
        $stmt->bind_param("ii", $student_number, $record_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $report_id = null;

        if ($result->num_rows > 0) {
            $report_row = $result->fetch_assoc();
            $report_id = $report_row['report_id'];
        }


        $query_check = "SELECT attendance_id FROM student_attendance WHERE student_number = ? AND record_id = ?";
        $stmt = $con->prepare($query_check);
        $stmt->bind_param("ii", $student_number, $record_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $query_update = "UPDATE student_attendance SET status = ?, report_id = ? WHERE student_number = ? AND record_id = ?";
            $stmt = $con->prepare($query_update);
            $stmt->bind_param("siii", $status, $report_id, $student_number, $record_id);
        } else {
            $query_insert = "INSERT INTO student_attendance (status, class_id, student_number, record_id, report_id) VALUES (?, ?, ?, ?, ?)";
            $stmt = $con->prepare($query_insert);
            $stmt->bind_param("siiii", $status, $class_id, $student_number, $record_id, $report_id);
        }

        if (!$stmt->execute()) {
            echo "Error saving attendance for student $student_number.";
        }
    }

    header("Location: viewrecord.php?record_id=" . urlencode($record_id));
    exit;
} else {
    echo "Invalid request method.";
}
?>