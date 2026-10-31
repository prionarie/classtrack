<?php
include "../assets/db/dbcon.php";
session_start();

if (!isset($_SESSION['employee_id'])) {
  header("Location: instructors/welcome/login.php");
  exit;
}

$employee_id = $_SESSION['employee_id'];

if (isset($con) && $con) {
  $stmt = $con->prepare("SELECT first_name FROM instructors WHERE employee_id = ?");
  $stmt->bind_param("i", $employee_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $user_name = 'Instructor';
  if ($row = $result->fetch_assoc()) {
      $user_name = $row['first_name'];
  }
} else {
  $user_name = 'Instructor';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Instructor Dashboard | [Placeholder] Class Attendance Report System</title>
  <link rel="stylesheet" href="../assets/css/index.css">
</head>
<body>

<div class="navbar">
  <img src="../assets/imgs/[Placeholder]-logo.png" alt="[Placeholder] Logo">
  <div class="nav-items">
    <a class="nav-item" href="index.php">🏠 HOME</a>
    <a class="nav-item" href="instructors/lists/classlist.php">📚 CLASSES</a>
    <a class="nav-item" href="instructors/lists/attendancelist.php">🗓️ ATTENDANCE</a>
    <a class="nav-item" href="instructors/lists/reportlist.php">📁 REPORTS</a>
    </div>
    <div class="dropdown-container">
    <span id="accountToggle">👤 <?= htmlspecialchars($user_name) ?> <span id="arrowIcon">&#9660;</span></span>
      <div class="dropdown-content" id="accountDropdown">
        <a href="instructors/account/viewaccount.php">✏️ View Account</a>
        <a href="instructors/account/editpassword.php">🔐 Change Password</a>
        <a href="instructors/account/logout.php" style="color: red;">🚪 Log Out</a>
      </div>
  </div>
</div>

<div class="dashboard">
  <h1>[Placeholder] CLASS ATTENDANCE REPORT SYSTEM</h1>
  <h2>INSTRUCTOR DASHBOARD</h2>
    <div class="dashboard-box">
      <div class="card-grid">

        <div class="column">
          <div class="card">
            <h2>➕ Create Class</h2>
            <p>Register a new class</p>
            <button onclick="location.href='instructors/classes/addclass.php'">Add</button>
          </div>
          <div class="card">
            <h2>📚 Class List</h2>
            <p>View list of classes and students</p>
            <button onclick="location.href='instructors/lists/classlist.php'">View</button>
          </div>
          <div class="card">
            <h2>📁 Absence Reports</h2>
            <p>View submitted absence reports</p>
            <button onclick="location.href='instructors/lists/reportlist.php'">View</button>
          </div>
        </div>

        <div class="column">
          <div class="card">
            <h2>📝 Create Attendance</h2>
            <p>Create a new attendance record</p>
            <button onclick="location.href='instructors/attendance/addrecord.php'">Create</button>
          </div>
          <div class="card">
            <h2>🗓️ Attendance Records</h2>
            <p>View list of attendance records</p>
            <button onclick="location.href='instructors/lists/attendancelist.php'">View</button>
          </div>
        </div>

      </div>
    </div>
</div>

<script src="../assets/js/dropdown.js"></script>

</body>
</html>