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

$stmt = $con->prepare("SELECT * FROM students WHERE student_number = ?");
$stmt->bind_param("i", $student_number);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Account not found.");
}
$student = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $student_type = $_POST['student_type'];
    $section = $_POST['section'];
    $email = $_POST['email'];
    
    $stmt = $con->prepare("UPDATE students SET first_name = ?, middle_name = ?, last_name = ?, student_type = ?, section = ?, email = ? WHERE student_number = ?");
    $stmt->bind_param("ssssssi", $first_name, $middle_name, $last_name, $student_type, $section, $email, $student_number);

    if ($stmt->execute()) {
      header("Location: viewaccount.php");
      exit;
    } else {
      echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Update Account | School Class Attendance Report System</title>
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

<a href="viewaccount.php" class="go-back">Go Back</a>

<div class="page-container">
  <div class="container-box-big">
    <div class="container-header">
      <h2>Update Account</h2>
    </div>

    <form method="POST">
      <div class="form-group">
        <div>
          <label for="first_name">First Name</label>
          <input type="text" name="first_name" id="first_name" value="<?= htmlspecialchars($student['first_name']) ?>" required>
        </div>
        <div>
          <label for="middle_name">Middle Name</label>
          <input type="text" name="middle_name" id="middle_name" value="<?= htmlspecialchars($student['middle_name']) ?>">
        </div>
        <div>
          <label for="last_name">Last Name</label>
          <input type="text" name="last_name" id="last_name" value="<?= htmlspecialchars($student['last_name']) ?>" required>
        </div>
      </div>

      <div class="form-group">
        <div>
          <label for="student_type">Student Type</label>
          <select name="student_type" id="student_type" required>
            <option value="Regular" <?= $student['student_type'] === 'Regular' ? 'selected' : '' ?>>Regular</option>
            <option value="Irregular" <?= $student['student_type'] === 'Irregular' ? 'selected' : '' ?>>Irregular</option>
          </select>
        </div>
        <div>
          <label for="section">Section</label>
          <input type="text" name="section" id="section" value="<?= htmlspecialchars($student['section']) ?>" required>
        </div>
      </div>

      <div class="form-group">
        <div>
          <label for="email">Email</label>
          <input type="email" name="email" id="email" value="<?= htmlspecialchars($student['email']) ?>" required>
        </div>
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