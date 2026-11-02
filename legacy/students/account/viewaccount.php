<?php
include "../../config/dbcon.php";
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

$sql = "SELECT * FROM students WHERE student_number = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $student_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
  $student = $result->fetch_assoc();
} else {
  die("Account not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account Information | School Class Attendance Report System</title>
  <link rel="stylesheet" href="../../assets/css/account.css">
</head>
<body>

<div class="navbar">
  <img src="../../assets/imgs/school-logo.png" alt="School Logo">
  <div class="nav-items">
    <a class="nav-item" href="../index.php">🏠 HOME</a>
    <a class="nav-item" href="../lists/classlist.php">📚 CLASSES</a>
    <a class="nav-item" href="../attendance/selectclass.php">🗓️ ATTENDANCE</a>
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

<a href="../index.php" class="go-back">Go Back</a>

<div class="page-container">
  <div class="container-box-big">
    <div class="container-header">
      <h2>Account Information</h2>
    </div>
    <div class="form-group">
        <div>
            <label for="student_number">Student Number</label>
            <input type="text" name="student_number" id="student_number" value="<?php echo htmlspecialchars($student['student_number']); ?>" readonly>
        </div>
    </div>
    
    <div class="form-group">
        <div>    
            <label for="first_name">First Name</label>
            <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($student['first_name']); ?>" readonly>
        </div>
        <div>
            <label for="middle_name">Middle Name</label>
            <input type="text" name="middle_name" id="middle_name" value="<?php echo htmlspecialchars($student['middle_name']); ?>" readonly>
        </div>
        <div>
            <label for="last_name">Last Name</label>
            <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($student['last_name']); ?>" readonly>
        </div>
    </div>
    
    <div class="form-group">
        <div>
            <label for="student_type">Student Type</label>
            <input type="text" id="student_type" name="student_type" value="<?php echo htmlspecialchars($student['student_type']); ?>" readonly>
        </div>
        <div>
            <label for="section">Section</label>
            <input type="text" name="section" id="section" value="<?php echo htmlspecialchars($student['section']); ?>" readonly>
        </div>
    </div>

    <div class="form-group">
      <div>
        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($student['email']); ?>" readonly>
      </div>
    </div>
    
    <div class="form-group">
      <button onclick="window.location.href='editaccount.php'">Update Account</a>
      <button onclick="window.location.href='editpassword.php'">Change Password</a>
    </div>
  </form>
</div>
</div>

<script src="../../assets/js/dropdown.js"></script>

</body>
</html>