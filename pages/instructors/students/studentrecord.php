<?php
include "../../../assets/db/dbcon.php";
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

$class_id = $_GET['class_id'] ?? null;
$student_number = $_GET['student_number'] ?? null;

if (!$class_id || !$student_number) {
    echo "Class ID and Student Number are required.";
    exit;
}

$stmt = $con->prepare("
    SELECT s.student_number, s.first_name, s.middle_name, s.last_name, s.section, c.course_code
    FROM students s
    JOIN student_classes sc ON s.student_number = sc.student_number
    JOIN classes c ON sc.class_id = c.class_id
    WHERE sc.class_id = ? AND s.student_number = ?
");
$stmt->bind_param("ii", $class_id, $student_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Student not found in this class.";
    exit;
}

$student = $result->fetch_assoc();
$course_code = $student['course_code'];

$query_attendance = "
  SELECT 
    sa.status,
    ar.date,
    c.course_code,
    ar.record_id,
    ar.class_id,
    ab.report_id,
    ab.remarks AS report_remarks
  FROM student_attendance sa
  JOIN attendance_records ar ON sa.record_id = ar.record_id
  JOIN classes c ON sa.class_id = c.class_id
  LEFT JOIN absence_reports ab ON sa.report_id = ab.report_id
  WHERE sa.student_number = ? AND sa.class_id = ?
  ORDER BY ar.date DESC
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
  <title>View Student Attendance Record | [Placeholder] Class Attendance Report System</title>
  <link rel="stylesheet" href="../../../assets/css/list.css">
</head>
<body>

<div class="navbar">
  <img src="../../../assets/imgs/[Placeholder]-logo.png" alt="[Placeholder] Logo">
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

<a href="../lists/studentlist.php?class_id=<?= urlencode($class_id) ?>" class="go-back">Go Back</a>

<div class="page-container">
  <div class="container-box">
    <div class="container-header">
      <h2>View Student Attendance Record</h2>
    </div>

    <div class="class-info">
      <div class="class-box">
        <p><strong>Name: </strong><?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name'] . ' ' . $student['middle_name']) ?></p>
      </div>
      <div class="class-box">
        <p><strong>Student Number: </strong><?= htmlspecialchars($student['student_number']) ?></p>
      </div>
    </div>
    <div class="class-info">
      <div class="class-box">
        <p><strong>Section: </strong><?= htmlspecialchars($student['section']) ?></p>
      </div>
      <div class="class-box">
        <p><strong>Course Code: </strong><?= htmlspecialchars($course_code) ?></p>
      </div>
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
              if (empty($remarks) && !empty($row['report_id']) && $row['report_remarks'] === 'Pending') {
                $remarks = 'Pending';
                $remarkClass = 'status-pending';
              } elseif ($remarks === 'Validated') {
                $remarkClass = 'status-validated';
              } elseif ($remarks === 'Rejected') {
                $remarkClass = 'status-rejected';
              } else {
                $remarkClass = 'status-default';
              }
              ?>
              <span class="<?= $remarkClass; ?>">
                <?= htmlspecialchars($remarks ?: '—'); ?>
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

<script src="../../../assets/js/dropdown.js"></script>

</body>
</html>