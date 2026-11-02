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

$class_id = $_GET['class_id'] ?? '';
$search = $_GET['search'] ?? '';
$searchParam = "%$search%";

if (!empty($search)) {
  $stmt = $con->prepare("
  SELECT s.student_number, s.student_type, s.first_name, s.middle_name, s.last_name
  FROM students s
  INNER JOIN student_classes sc ON s.student_number = sc.student_number
  WHERE (s.student_number LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ?) 
  AND sc.class_id = ?
  ORDER BY s.last_name, s.first_name
");
  $stmt->bind_param("sssi", $searchParam, $searchParam, $searchParam, $class_id);
} else {
  $stmt = $con->prepare("
  SELECT s.student_number, s.student_type, s.first_name, s.middle_name, s.last_name
  FROM students s
  INNER JOIN student_classes sc ON s.student_number = sc.student_number
  WHERE sc.class_id = ?
  ORDER BY s.last_name, s.first_name
");
  $stmt->bind_param("i", $class_id);
}

$stmt->execute();
$students_result = $stmt->get_result();
$total_students = $students_result->num_rows;

$class_stmt = $con->prepare("
  SELECT c.course_code, c.section
  FROM classes c
  WHERE c.class_id = ?
");
$class_stmt->bind_param("i", $class_id);
$class_stmt->execute();
$class_result = $class_stmt->get_result();
$class_row = $class_result->fetch_assoc();
$course_code = $class_row['course_code'];
$section = $class_row['section'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Information List | School Class Attendance Report System</title>
  <link rel="stylesheet" href="../../assets/css/list.css">
</head>
<body>

<div class="navbar">
  <img src="../../assets/imgs/school-logo.png" alt="School Logo">
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
    <a href="classlist.php" class="go-back">Go Back</a>
    <form method="GET" style="display: flex; align-items: center;">
    <input type="hidden" name="class_id" value="<?= htmlspecialchars($class_id) ?>">  
    <input type="text" name="search" class="search-bar" placeholder="🔍 Search Students"
    value="<?= htmlspecialchars($search) ?>">
      <button type="submit" class="search-button">Search</button>
    </form>
</div>

<div class="page-container">
  <div class="container-box">

    <div class="container-header">
      <h2>Student Information List</h2>
    </div>

    <div class="class-info">
      <div class="class-box">
        <p><strong>Course Code: </strong><?= htmlspecialchars($course_code) ?></p>
      </div>
      <div class="class-box">
        <p><strong>Section: </strong><?= htmlspecialchars($section) ?></p>
      </div>
      <div class="class-box">
        <p><strong>Total Students: </strong><?= $total_students ?></p>
      </div>
    </div>

    <div>
      <button class="btn add" onclick="window.location.href='../students/addstudent.php?class_id=<?= htmlspecialchars($class_id) ?>'">+ Add Student</button>
    </div>
    
  </div>
  <?php $seq = 1; ?>
  <table>
    <tr>
      <th>No.</th>
      <th>Student Number</th>
      <th>Last Name</th>
      <th>First Name</th>
      <th>Middle Name</th>
      <th>Student Type</th>
      <th>Actions</th>
    </tr>
    <?php while ($row = $students_result->fetch_assoc()): ?>
      <tr>
          <td><?= $seq++ ?></td>
          <td><?= htmlspecialchars($row['student_number']) ?></td>
          <td><?= htmlspecialchars($row['last_name']) ?></td>
          <td><?= htmlspecialchars($row['first_name']) ?></td>
          <td><?= htmlspecialchars($row['middle_name']) ?></td>
          <td><?= htmlspecialchars($row['student_type']) ?></td>
          <td>
          <button onclick="window.location.href='../students/studentrecord.php?student_number=<?= urlencode($row['student_number']) ?>&class_id=<?= urlencode($class_id) ?>'">View Attendance Record</button>
            <form action="../students/deletestudent.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this student?');">
              <input type="hidden" name="student_number" value="<?= htmlspecialchars($row['student_number']) ?>">
              <input type="hidden" name="class_id" value="<?= htmlspecialchars($class_id) ?>">
              <button class="btn delete" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
    </table>
</div>

<script src="../../assets/js/dropdown.js"></script>

</body>
</html>