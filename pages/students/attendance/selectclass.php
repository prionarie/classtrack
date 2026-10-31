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
    SELECT c.class_id, c.course_code, c.course_title, c.section
    FROM classes c
    JOIN student_classes sc ON c.class_id = sc.class_id
    WHERE sc.student_number = ?
    ORDER BY c.course_code ASC
");
$stmt->bind_param("i", $student_number);
$stmt->execute();
$classes = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Select Attedance Record | [Placeholder] Class Attendance Report System</title>
  <link rel="stylesheet" href="../../../assets/css/addrecord.css">
</head>
<body>

<div class="navbar">
  <img src="../../../assets/imgs/[Placeholder]-logo.png" alt="[Placeholder] Logo">
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

<a href="../index.php" class="go-back">Go Back</a>

<div class="page-container">
  <div class="container-box">
    <div class="container-header">
      <h2>Select Attendance Record</h2>
    </div>

    <form action="viewrecord.php" method="GET">
      <div class="form-group">
        <label for="class_id">Select Class</label>
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
        <button type="submit">VIEW ATTENDANCE RECORD</button>
      </div>
    </form>
  </div>
</div>

<script src="../../../assets/js/dropdown.js"></script>

</body>
</html>