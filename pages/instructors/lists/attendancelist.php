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

$search = $_GET['search'] ?? '';
$searchParam = "%$search%";

if (!empty($search)) {
  $stmt = $con->prepare("
    SELECT ar.record_id, ar.date, c.course_code, c.section
    FROM attendance_records ar
    INNER JOIN classes c ON ar.class_id = c.class_id
    INNER JOIN instructor_classes ic ON c.class_id = ic.class_id
    WHERE ic.employee_id = ?
      AND (c.course_code LIKE ? OR c.section LIKE ?)
    ORDER BY ar.date DESC
  ");
  $stmt->bind_param("sss", $employee_id, $searchParam, $searchParam);
} else {
  $stmt = $con->prepare("
    SELECT ar.record_id, ar.date, c.course_code, c.section
    FROM attendance_records ar
    INNER JOIN classes c ON ar.class_id = c.class_id
    INNER JOIN instructor_classes ic ON c.class_id = ic.class_id
    WHERE ic.employee_id = ?
    ORDER BY ar.date DESC
  ");
  $stmt->bind_param("s", $employee_id);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Attendance Record List | [Placeholder] Class Attendance Report System</title>
  <link rel="stylesheet" href="../../../assets/css/list.css">
</head>
<body>

<div class="navbar">
  <img src="../../../assets/imgs/[Placeholder]-logo.png" alt="[Placeholder] Logo">
  <div class="nav-items">
    <a class="nav-item" href="../index.php">🏠 HOME</a>
    <a class="nav-item" href="classlist.php">📚 CLASSES</a>
    <a class="nav-item" href="attendancelist.php">🗓️ ATTENDANCE</a>
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
      <input type="text" name="search" class="search-bar" placeholder="🔍 Search Records"
      value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
      <button type="submit" class="search-button">Search</button>
    </form>
</div>

<div class="page-container">
  <div class="container-box">
    <div class="container-header">
      <h2>Attendance Record List</h2>
    </div>

    <div>
      <button class="btn add" onclick="window.location.href='../attendance/addrecord.php'">+ Add Record</button>
    </div>

  </div>
    <table>
    <tr>
        <th>Record ID</th>
        <th>Date</th>
        <th>Course Code</th>
        <th>Section</th>
        <th>Present/Total</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <?php
            $record_id = $row['record_id'];

            $classIdStmt = $con->prepare("SELECT class_id FROM attendance_records WHERE record_id = ?");
            $classIdStmt->bind_param("i", $record_id);
            $classIdStmt->execute();
            $classIdResult = $classIdStmt->get_result();
            $class_id = $classIdResult->fetch_assoc()['class_id'];

            $totalStmt = $con->prepare("SELECT COUNT(*) as total FROM student_classes WHERE class_id = ?");
            $totalStmt->bind_param("i", $class_id);
            $totalStmt->execute();
            $total = $totalStmt->get_result()->fetch_assoc()['total'];

            $presentStmt = $con->prepare("SELECT COUNT(*) as present FROM student_attendance WHERE record_id = ? AND status IN ('Present', 'Late')");
            $presentStmt->bind_param("i", $record_id);
            $presentStmt->execute();
            $present = $presentStmt->get_result()->fetch_assoc()['present'];
            $isComplete = ($present == $total);
        ?>
        <tr>
            <td><?= htmlspecialchars($record_id) ?></td>
            <td><?= htmlspecialchars($row['date']) ?></td>
            <td><?= htmlspecialchars($row['course_code']) ?></td>
            <td><?= htmlspecialchars($row['section']) ?></td>
            <td class="<?= $isComplete ? 'complete' : '' ?>">
              <?= $present ?>/<?= $total ?>
            </td>
            <td>
                <button onclick="window.location.href='../attendance/viewrecord.php?record_id=<?= urlencode($record_id) ?>'">View</button>
                <form action="../attendance/deleterecord.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this record?');">
                    <input type="hidden" name="record_id" value="<?= htmlspecialchars($record_id) ?>">
                    <button class="btn delete" type="submit">Delete</button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
</table>
  </div>
</div>

<script src="../../../assets/js/dropdown.js"></script>

</body>
</html>