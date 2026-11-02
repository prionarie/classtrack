<?php
include "../../config/dbcon.php";
session_start();

if (!isset($_SESSION['student_number'])) {
  header("Location: welcome/login.php");
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Dashboard | School Class Attendance Report System</title>
  <link rel="stylesheet" href="../../assets/css/index.css">
</head>
<body>

<div class="navbar">
  <img src="../../assets/imgs/school-logo.png" alt="School Logo">
  <div class="nav-items">
    <a class="nav-item" href="index.php">🏠 HOME</a>
    <a class="nav-item" href="lists/classlist.php">📚 CLASSES</a>
    <a class="nav-item" href="attendance/selectclass.php">🗓️ ATTENDANCE</a>
    <a class="nav-item" href="lists/reportlist.php">📁 REPORTS</a>
    </div>
    <div class="dropdown-container">
    <span id="accountToggle">👤 <?= htmlspecialchars($user_name) ?> <span id="arrowIcon">&#9660;</span></span>
      <div class="dropdown-content" id="accountDropdown">
        <a href="account/viewaccount.php">✏️ View Account</a>
        <a href="account/editpassword.php">🔐 Change Password</a>
        <a href="account/logout.php" style="color: red;">🚪 Log Out</a>
      </div>
  </div>
</div>

<div class="dashboard">
  <h1>School CLASS ATTENDANCE REPORT SYSTEM</h1>
  <h2>STUDENT DASHBOARD</h2>
    <div class="dashboard-box">
      <div class="card-grid">

        <div class="column">
          <div class="card">
            <h2>➕ Join Class</h2>
            <p>Enroll in a class</p>
            <button onclick="location.href='classes/joinclass.php'">Join</button>
          </div>
          <div class="card">
            <h2>📝 Create Absence Report</h2>
            <p>Submit a new absence report</p>
            <button onclick="location.href='reports/createreport.php'">Create</button>
          </div>
        </div>

        <div class="column">
          <div class="card">
            <h2>📚 View Classes</h2>
            <p>View classes and attendance history</p>
            <button onclick="location.href='lists/classlist.php'">View</button>
          </div>
          <div class="card">
            <h2>📁 View Absence Reports</h2>
            <p>Review submitted absence reports</p>
            <button onclick="location.href='lists/reportlist.php'">View</button>
          </div>
        </div>

      </div>
    </div>
</div>

<script src="../../assets/js/dropdown.js"></script>

</body>
</html>