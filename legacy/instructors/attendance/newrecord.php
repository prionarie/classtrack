<?php
session_start();
include '../../config/dbcon.php';

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

$record_id = $_GET['record_id'];

$query_record = "SELECT r.class_id, r.date, c.course_code, c.section 
                 FROM attendance_records r 
                 JOIN classes c ON r.class_id = c.class_id 
                 WHERE r.record_id = ?";
$stmt = $con->prepare($query_record);
$stmt->bind_param("i", $record_id);
$stmt->execute();
$record_result = $stmt->get_result();

if ($record_result->num_rows > 0) {
    $record = $record_result->fetch_assoc();
    $course_code = $record['course_code'];
    $section = $record['section'];
    $date = $record['date'];
} else {
    echo "Attendance record not found.";
    exit;
}

$query_students = "SELECT s.student_number, s.first_name, s.middle_name, s.last_name, sa.status, ar.report_id, ar.remarks AS report_remarks
                   FROM student_classes sc
                   JOIN students s ON sc.student_number = s.student_number
                   LEFT JOIN student_attendance sa ON sa.record_id = ? AND sa.student_number = s.student_number
                   LEFT JOIN absence_reports ar ON ar.record_id = ? AND ar.student_number = s.student_number
                   WHERE sc.class_id = ?
                   ORDER BY s.last_name ASC";
$stmt = $con->prepare($query_students);
$stmt->bind_param("iii", $record_id, $record_id, $record['class_id']);
$stmt->execute();
$students_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Attendance Record | School Class Attendance Report System</title>
  <link rel="stylesheet" href="../../assets/css/list.css">
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

<a href="addrecord.php" class="go-back">Go Back</a>

<div class="page-container">
  <div class="container-box">
    <div class="container-header">
      <h2>New Attendance Record</h2>
    </div>

    <div class="class-info">
      <div class="class-box">
        <p><strong>Course Code: </strong><?= htmlspecialchars($course_code) ?></p>
      </div>
      <div class="class-box">
        <p><strong>Section: </strong><?= htmlspecialchars($section) ?></p>
      </div>
      <div class="class-box">
        <p><strong>Date: </strong><?= htmlspecialchars($date) ?></p>
      </div>
    </div>
  </div>

<form action="saverecord.php" method="POST">
  <input type="hidden" name="class_id" value="<?= $class_id ?>">
  <input type="hidden" name="record_id" value="<?= $record_id ?>">
  <input type="hidden" name="date" value="<?= $date ?>">
  <?php $seq = 1; ?>
    <table>
        <tr>
            <th>No.</th>
            <th>Student Number</th>
            <th>Last Name</th>
            <th>First Name</th>
            <th>Middle Name</th>
            <th>Status</th>
            <th>Remarks</th>
            <th>Reports</th>
        </tr>
        <?php while ($row = $students_result->fetch_assoc()): ?>
            <tr>
                <td><?= $seq++ ?></td>
                <td><?= htmlspecialchars($row['student_number']) ?></td>
                <td><?= htmlspecialchars($row['last_name']) ?></td>
                <td><?= htmlspecialchars($row['first_name']) ?></td>
                <td><?= htmlspecialchars($row['middle_name']) ?></td>
                <td>
                <?php
                $statuses = ['Present', 'Absent', 'Late'];
                $colors = ['Present' => 'green', 'Absent' => 'red', 'Late' => 'goldenrod'];
                ?>
                <?php foreach ($statuses as $status): ?>
                  <label style="color: <?= $colors[$status] ?>;">
                    <input type="radio"
                    name="status[<?= $row['student_number'] ?>]"
                    value="<?= $status ?>"
                    <?= ($row['status'] === $status) ? 'checked' : '' ?>>
                    <?= $status ?>
                  </label>
                <?php endforeach; ?>
                </td>
                <td>
                <?php
                $remarks = $row['remarks'] ?? '';
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
    </table>
    <button type="submit" class="btn save">Save</button>

    </form>
</div>

<script src="../../assets/js/dropdown.js"></script>

</body>
</html>