<?php
include "../../config/dbcon.php";
session_start();

if (!isset($_SESSION['employee_id'])) {
  header("Location: ../welcome/login.php");
  exit;
}

$employee_id = $_SESSION['employee_id'];

$stmt = $con->prepare("SELECT first_name FROM instructors WHERE employee_id = ?");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$user_name = 'Instructor';
if ($row = $result->fetch_assoc()) {
    $user_name = $row['first_name'];
}

if (!isset($_GET['report_id'])) {
    die("No report ID provided.");
}
$report_id = $_GET['report_id'];

$stmt = $con->prepare("SELECT ar.*, c.course_code, c.section, r.date, s.first_name, s.middle_name, s.last_name
                       FROM absence_reports ar
                       JOIN classes c ON ar.class_id = c.class_id
                       JOIN attendance_records r ON ar.record_id = r.record_id
                       JOIN students s ON ar.student_number = s.student_number
                       WHERE ar.report_id = ?");
$stmt->bind_param("i", $report_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Report not found.");
}

$report = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Absence Report | School Class Attendance Report System</title>
  <link rel="stylesheet" href="../../assets/css/report.css">
</head>
<body>

<div class="navbar">
  <img src="../../assets/imgs/school-logo.png" alt="School Logo">
  <div class="nav-items">
    <a class="nav-item" href="../index.php">🏠 HOME</a>
    <a class="nav-item" href="../lists/classlist.php">📚 CLASSES</a>
    <a class="nav-item" href="../lists/attendancelist.php">🗓️ ATTENDANCE</a>
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

<a href="../lists/reportlist.php?" class="go-back">Go Back</a>

<div class="page-container">
  <div class="container-box">
    <div class="container-header">
      <h2>View Absence Report</h2>
    </div>
    <div class="class-info">
      <div class="info-group">
        <label>Name</label>
        <div class="info-box"><?= htmlspecialchars($report['last_name'] . ', ' . $report['first_name'] . ' ' . $report['middle_name']) ?></div>
      </div>
    <div class="info-group">
      <label>Section</label>
      <div class="info-box"><?= htmlspecialchars($report['section']) ?></div>
    </div>
    <div class="info-group">
      <label>Course Code</label>
      <div class="info-box"><?= htmlspecialchars($report['course_code']) ?></div>
    </div>
    <div class="info-group">
      <label>Date</label>
      <div class="info-box"><?= htmlspecialchars($report['date']) ?></div>
    </div>
  </div>

  <div class="form-group">
      <label for="reason">Reason</label>
      <textarea id="reason" rows="5" readonly><?= htmlspecialchars($report['reason']) ?></textarea>
    </div>

    <div class="form-group">
      <label>Supporting Document</label>
      <?php if (!empty($report['file_upload'])): ?>
        <p><a href="<?= htmlspecialchars($report['file_upload']) ?>" target="_blank">View Uploaded File</a></p>
      <?php else: ?>
        <p>No document uploaded.</p>
      <?php endif; ?>
    </div>
  </div>

    <form action="savereport.php" method="POST">
      <input type="hidden" name="report_id" value="<?= $report['report_id'] ?>">
      <div class="form-group">
        <label for="feedback">Feedback:</label>
        <textarea class="feedback" name="feedback" id="feedback" rows="4" placeholder="Enter feedback (optional)"><?= htmlspecialchars($report['feedback'] ?? '') ?></textarea>
      </div>
      <div class="button-group">
        <button class="validate" type="submit" name="action" value="Validated">Validate</button>
        <button class="reject" type="submit" name="action" value="Rejected">Reject</button>
      </div>
    </form>
  </div>
</div>

<script src="../../assets/js/dropdown.js"></script>

</body>
</html>