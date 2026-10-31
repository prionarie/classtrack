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

$stmt = $con->prepare("
    SELECT c.class_id, c.course_title, c.course_code, c.section, c.class_schedule
    FROM student_classes sc
    JOIN classes c ON sc.class_id = c.class_id
    WHERE sc.student_number = ?
    ORDER BY class_id ASC
");
$stmt->bind_param("i", $student_number);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Class List | [Placeholder] Class Attendance Report System</title>
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
</div>

<div class="page-container">
  <div class="container-box">
    <div class="container-header">
      <h2>View Class List</h2>
    </div>

    <div>
      <button class="btn add" onclick="window.location.href='../classes/joinclass.php'">+ Join Class</button>
    </div>

  </div>

  <table>
    <tr>
      <th>Class ID</th>
      <th>Course Title</th>
      <th>Course Code</th>
      <th>Section</th>
      <th>Class Schedule</th>
      <th>Actions</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['class_id']) ?></td>
        <td><?= htmlspecialchars($row['course_title']) ?></td>
        <td><?= htmlspecialchars($row['course_code']) ?></td>
        <td><?= htmlspecialchars($row['section']) ?></td>
        <td><?= htmlspecialchars($row['class_schedule']) ?></td>
        <td>
          <button onclick="window.location.href='../attendance/viewrecord.php?class_id=<?= $row['class_id'] ?>'">View Attendance Record</button>
          <form action="../classes/leaveclass.php" method="POST" onsubmit="return confirm('Are you sure you want to leave this class?');">
            <input type="hidden" name="student_number" value="<?= htmlspecialchars($student_number) ?>">
            <input type="hidden" name="class_id" value="<?= htmlspecialchars($row['class_id']) ?>">
          <button class="btn delete" type="submit">Leave Class</button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
</div>

<script src="../../../assets/js/dropdown.js"></script>

</body>
</html>