<?php
include "../../../assets/db/dbcon.php";
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

$search = $_GET['search'] ?? '';
$searchParam = "%$search%";

if (!empty($search)) {
    $stmt = $con->prepare("
        SELECT ar.report_id, c.course_code, c.section, s.last_name, s.first_name, s.middle_name, r.date, ar.remarks
        FROM absence_reports ar
        INNER JOIN students s ON ar.student_number = s.student_number
        INNER JOIN classes c ON ar.class_id = c.class_id
        INNER JOIN attendance_records r ON ar.record_id = r.record_id
        WHERE ar.student_number = ? AND (
            c.course_code LIKE ? OR
            c.section LIKE ? OR
            s.last_name LIKE ? OR
            s.first_name LIKE ? OR
            s.middle_name LIKE ?
        )
        ORDER BY r.date DESC
    ");
    $stmt->bind_param("isssss", $student_number, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam);
} else {
    $stmt = $con->prepare("
        SELECT ar.report_id, c.course_code, c.section, s.last_name, s.first_name, s.middle_name, r.date, ar.remarks
        FROM absence_reports ar
        INNER JOIN students s ON ar.student_number = s.student_number
        INNER JOIN classes c ON ar.class_id = c.class_id
        INNER JOIN attendance_records r ON ar.record_id = r.record_id
        WHERE ar.student_number = ?
        ORDER BY r.date DESC
    ");
    $stmt->bind_param("i", $student_number);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Submitted Absence Reports | [Placeholder] Class Attendance Report System</title>
  <link rel="stylesheet" href="../../../assets/css/list.css">
</head>
<body>

<div class="navbar">
  <img src="../../../assets/imgs/[Placeholder]-logo.png" alt="[Placeholder] Logo">
  <div class="nav-items">
    <a class="nav-item" href="../index.php">🏠 HOME</a>
    <a class="nav-item" href="classlist.php">📚 CLASSES</a>
    <a class="nav-item" href="../attendance/selectclass.php">🗓️ ATTENDANCE</a>
    <a class="nav-item" href="reportlist.php">📁 REPORTS</a>
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
    <form method="GET" style="display: flex; align-items: center;">
      <input type="text" name="search" class="search-bar" placeholder="🔍 Search Classes"
      value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
      <button type="submit" class="search-button">Search</button>
    </form>
</div>
<div class="page-container">
  <div class="container-box">
    <div class="container-header">
      <h2>Submitted Absence Reports</h2>
    </div>

    <div>
      <button class="btn add" onclick="window.location.href='../reports/createreport.php'">Create Report</button>
    </div>

  </div>
  <table>
    <tr>
      <th>Report ID</th>
      <th>Course Code</th>
      <th>Section</th>
      <th>Last Name</th>
      <th>First Name</th>
      <th>Middle Name</th>
      <th>Date</th>
      <th>Remarks</th>
      <th>Actions</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['report_id']) ?></td>
        <td><?= htmlspecialchars($row['course_code']) ?></td>
        <td><?= htmlspecialchars($row['section']) ?></td>
        <td><?= htmlspecialchars($row['last_name']) ?></td>
        <td><?= htmlspecialchars($row['first_name']) ?></td>
        <td><?= htmlspecialchars($row['middle_name']) ?></td>
        <td><?= htmlspecialchars($row['date']) ?></td>
        <?php
        $remarks = $row['remarks'] ?? '';
        if ($remarks === 'Validated') {
          $remarkClass = 'status-validated';
        } elseif ($remarks === 'Rejected') {
          $remarkClass = 'status-rejected';
        } else {
          $remarks = 'Pending';
          $remarkClass = 'status-pending';
        }
        ?>
        <td><span class="<?= $remarkClass; ?>"><?= htmlspecialchars($row['remarks'] ?: '—') ?></span></td>
        <td>
          <button onclick="window.location.href='../reports/viewreport.php?report_id=<?= urlencode($row['report_id']) ?>'">View</button>
          <form action="../reports/deletereport.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this report?');">
            <input type="hidden" name="report_id" value="<?= htmlspecialchars($row['report_id']) ?>">
            <button type="submit" class="btn delete">Delete</button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
</div>

<script src="../../../assets/js/dropdown.js"></script>

</body>
</html>