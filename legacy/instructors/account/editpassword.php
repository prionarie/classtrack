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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $new_password = $_POST['new_password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password !== $confirm_password) {
        die("Passwords do not match.");
    }
    if (strlen($new_password) < 6) {
        die("Password must be at least 6 characters.");
    }
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = $con->prepare("UPDATE instructors SET password = ? WHERE employee_id = ?");
$stmt->bind_param("si", $hashed_password, $employee_id);

if ($stmt->execute()) {
  header("Location: viewaccount.php");
  exit;
} else {
  echo "Error: " . $stmt->error;
  }
 
$stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Update Password | School Class Attendance Report System</title>
  <link rel="stylesheet" href="../../assets/css/account.css">
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
        <a href="viewaccount.php">✏️ View Account</a>
        <a href="editpassword.php">🔐 Change Password</a>
        <a href="logout.php" style="color: red;">🚪 Log Out</a>
      </div>
  </div>
</div>

<a href="viewaccount.php" class="go-back">Go Back</a>

<div class="page-container">
  <div class="container-box">
    <div class="container-header">
      <h2>Change Password</h2>
    </div>
  
  <form method="POST">
    <div class="form-group">
        <label for="new_password">New Password</label>
        <input type="password" id="new_password" name="new_password" required minlength="6">
    </div>
    <div class="form-group">
      <label for="confirm_password">Confirm Password</label>
      <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
    </div>
    <div class="form-group">
        <button type="submit">Update</button>
    </div>
  </form>
  </div>
</div>

<script src="../../assets/js/dropdown.js"></script>

</body>
</html>