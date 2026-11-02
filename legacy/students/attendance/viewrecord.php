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

$class_id = $_GET['class_id'] ?? null;

if (!$class_id) {
    echo "Class ID is required.";
    exit;
}

$stmt = $con->prepare("SELECT student_number, first_name, middle_name, last_name, section FROM students WHERE student_number = ?");
$stmt->bind_param("i", $student_number);
$stmt->execute();
$student_result = $stmt->get_result();

if ($student_result->num_rows === 0) {
    echo "Student not found.";
    exit;
}
$student = $student_result->fetch_assoc();

$stmt = $con->prepare("SELECT course_code FROM classes WHERE class_id = ?");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$class_result = $stmt->get_result();

if ($class_result->num_rows === 0) {
    echo "Class not found.";
    exit;
}
$course_code = $class_result->fetch_assoc()['course_code'];

$query_attendance = "
    SELECT 
        r.record_id,
        r.date,
        sa.status,
        ar.report_id,
        ar.remarks AS report_remarks
    FROM attendance_records r
    LEFT JOIN student_attendance sa 
        ON sa.record_id = r.record_id AND sa.student_number = ?
    LEFT JOIN absence_reports ar 
        ON sa.report_id = ar.report_id
    WHERE r.class_id = ?
    ORDER BY r.date DESC
";
$stmt = $con->prepare($query_attendance);
$stmt->bind_param("ii", $student_number, $class_id);
$stmt->execute();
$attendance_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Attendance Record | School Class Attendance Report System</title>
  <link rel="stylesheet" href="../../assets/css/list.css">
</head>
<body>

<div class="navbar">
  <img src="../../assets/imgs/school-logo.png" alt="School Logo">
  <div class="nav-items">
    <a class="nav-item" href="../index.php">🏠 HOME</a>
    <a class="nav-item" href="../lists/classlist.php">📚 CLASSES</a>
    <a class="nav-item" href="selectclass.php">🗓️ ATTENDANCE</a>
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

<a href="../lists/classlist.php" class="go-back">Go Back</a>

<div class="page-container">
  <div class="container-box">
    <div class="container-header">
      <h2>View Attendance Record</h2>
    </div>

    <div class="class-info">
      <div class="class-box">
        <p><strong>Name:</strong> <?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name'] . ' ' . $student['middle_name']) ?></p>
      </div>
      <div class="class-box">
        <p><strong>Student Number: </strong><?= htmlspecialchars($student['student_number']) ?></p>
      </div>
    </div>
    <div class="class-info">
      <div class="class-box">
        <p><strong>Section:</strong> <?= htmlspecialchars($student['section']) ?></p>
      </div>
      <div class="class-box">
        <p><strong>Course Code:</strong> <?= htmlspecialchars($course_code) ?></p>
      </div>
    </div>

    <table>
      <tr>
        <th>Date</th>
        <th>Status</th>
        <th>Remarks</th>
        <th>Report</th>
      </tr>
      <?php if ($attendance_result->num_rows > 0): ?>
        <?php while ($row = $attendance_result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['date']) ?></td>
            <td>
              <?php
              $status = $row['status'] ?? '';
              if ($status === 'Present') {
                $statusClass = 'status-present';
              } elseif ($status === 'Absent') {
                $statusClass = 'status-absent';
              } elseif ($status === 'Late') {
                $statusClass = 'status-late';
              } else {
                $statusClass = 'status-default';
              }
              ?>
              <span class="<?= $statusClass; ?>">
                <?= htmlspecialchars($status ?: '—'); ?>
              </span>
            </td>
            <td>
              <?php
              $remarks = $row['report_remarks'] ?? '';
              if (!empty($row['report_id']) && $remarks === 'Pending') {
                $remarkClass = 'status-pending';
              } elseif ($remarks === 'Validated') {
                $remarkClass = 'status-validated';
              } elseif ($remarks === 'Rejected') {
                $remarkClass = 'status-rejected';
              } else {
                $remarks = '—';
                $remarkClass = 'status-default';
              }
              ?>
              <span class="<?= $remarkClass ?>">
                <?= htmlspecialchars($remarks) ?>
              </span>
            </td>
            <td>
              <?php if (!empty($row['report_id'])): ?>
                <a href="../reports/viewreport.php?report_id=<?= urlencode($row['report_id']) ?>">View</a>
              <?php else: ?>
              —
            <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php endif; ?>
    </table>
  </div>
</div>

<script src="../../assets/js/dropdown.js"></script>

</body>
</html>