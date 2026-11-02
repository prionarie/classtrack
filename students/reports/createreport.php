<?php
include "../../config/dbcon.php";
session_start();

if (!isset($_SESSION['student_number'])) {
    header("Location: ../welcome/login.php");
    exit;
}

$student_number = $_SESSION['student_number'];

$stmt = $con->prepare("SELECT first_name FROM students WHERE student_number = ?");
$stmt->bind_param("i", $student_number);
$stmt->execute();
$result = $stmt->get_result();
$user_name = 'Student';
if ($row = $result->fetch_assoc()) {
    $user_name = $row['first_name'];
}

$stmt = $con->prepare("
    SELECT c.class_id, c.course_code, c.course_title, c.section
    FROM classes c
    JOIN student_classes sc ON c.class_id = sc.class_id
    WHERE sc.student_number = ?
    ORDER BY c.course_code ASC
");
$stmt->bind_param("i", $student_number);
$stmt->execute();
$classes = $stmt->get_result();

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $class_id = $_POST['class_id'];
    $date = $_POST['date'];
    $reason = $_POST['reason'];
    $file_path = null;

    if (!empty($_FILES['file_upload']['name'])) {
        $upload_dir = "../../assets/uploads/";
        $file_name = basename($_FILES['file_upload']['name']);
        $sanitized_name = preg_replace("/[^A-Za-z0-9_\-\.]/", '_', $file_name);
        $target_path = $upload_dir . time() . "_" . $sanitized_name;

        if (move_uploaded_file($_FILES['file_upload']['tmp_name'], $target_path)) {
            $file_path = $target_path;
        } else {
            $message = "Failed to upload file.";
        }
    }

    $stmt = $con->prepare("SELECT record_id FROM attendance_records WHERE class_id = ? AND date = ?");
    $stmt->bind_param("is", $class_id, $date);
    $stmt->execute();
    $record_result = $stmt->get_result();

    if ($record_result->num_rows === 0) {
        $message = "No attendance record found for that class and date.";
    } else {
        $record = $record_result->fetch_assoc();
        $record_id = $record['record_id'];

        $stmt = $con->prepare("SELECT attendance_id FROM student_attendance WHERE class_id = ? AND student_number = ? AND record_id = ?");
        $stmt->bind_param("iii", $class_id, $student_number, $record_id);
        $stmt->execute();
        $attendance_result = $stmt->get_result();

        if ($attendance_result->num_rows === 0) {
            $message = "No attendance record found for this class and date.";
        } else {
            $attendance = $attendance_result->fetch_assoc();
            $attendance_id = $attendance['attendance_id'];

            $stmt = $con->prepare("INSERT INTO absence_reports (reason, file_upload, class_id, student_number, record_id, remarks) VALUES (?, ?, ?, ?, ?, 'Pending')");
            $stmt->bind_param("ssiii", $reason, $file_path, $class_id, $student_number, $record_id);

            if ($stmt->execute()) {
                $report_id = $con->insert_id;
                $update_stmt = $con->prepare("UPDATE student_attendance SET report_id = ? WHERE attendance_id = ?");
                $update_stmt->bind_param("ii", $report_id, $attendance_id);
                $update_stmt->execute();

                header("Location: ../lists/reportlist.php");
                exit;
            } else {
                $message = "Failed to submit report.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Submit Absence Report | School Class Attendance Report System</title>
  <link rel="stylesheet" href="../../assets/css/report.css">
</head>
<body>

<div class="navbar">
  <img src="../../assets/imgs/school-logo.png" alt="School Logo">
  <div class="nav-items">
    <a class="nav-item" href="../index.php">🏠 HOME</a>
    <a class="nav-item" href="../lists/classlist.php">📚 CLASSES</a>
    <a class="nav-item" href="../attendance/selectclass.php">🗓️ ATTENDANCE</a>
    <a class="nav-item" href="../lists/reportlist.php">📁 REPORTS</a>
    </div>
    <div class="dropdown-container">
    <span id="accountToggle">👤 <?= htmlspecialchars($user_name) ?> <span id="arrowIcon">&#9660;</span></span>
      <div class="dropdown-content" id="accountDropdown">
        <a href="../account/viewaccount.php">✏️ View Account</a>
        <a href="../account/editpassword.php">🔐 Change Password</a>
        <a href="../account/logout.php" style="color: red;">🚪 Log Out</a>
      </div>
  </div>
</div>

<div class="top-bar">
  <a href="../index.php" class="go-back">Go Back</a>
</div>

<div class="page-container">
  <div class="container-box">
    <div class="container-header">
      <h2>Submit Absence Report</h2>
    </div>

    <form method="POST" enctype="multipart/form-data">

      <div class="form-group">
        <label for="class_id">Select Class<span class="required">*</span></label>
        <select name="class_id" id="class_id" required>
          <option value="">---</option>
          <?php while ($row = $classes->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($row['class_id']) ?>">
              <?= htmlspecialchars($row['course_code']) ?> - <?= htmlspecialchars($row['section']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="date">Date of Absence<span class="required">*</span></label>
        <input type="date" name="date" id="date" required>
      </div>

      <div class="form-group">
        <label for="reason">Reason<span class="required">*</span></label>
        <textarea name="reason" id="reason" rows="5" required></textarea>
      </div>

      <div class="form-group">
        <label for="file_upload">Upload Supporting Document</label>
        <input type="file" name="file_upload" id="file_upload" accept=".jpg,.jpeg,.png,.pdf,.docx">
      </div>

      <div class="form-group">
        <button type="submit" class="submit">Submit</button>
      </div>
    </form>
  </div>
</div>

<script src="../../assets/js/dropdown.js"></script>

</body>
</html>