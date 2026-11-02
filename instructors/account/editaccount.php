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

$stmt = $con->prepare("SELECT * FROM instructors WHERE employee_id = ?");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Account not found.");
}
$instructor = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $department = $_POST['department'];
    $position = $_POST['position'];
    $email = $_POST['email'];
    
    $stmt = $con->prepare("UPDATE instructors SET first_name = ?, middle_name = ?, last_name = ?, department = ?, position = ?, email = ? WHERE employee_id = ?");
    $stmt->bind_param("ssssssi", $first_name, $middle_name, $last_name, $department, $position, $email, $employee_id);

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
  <div class="container-box-big">
    <div class="container-header">
      <h2>Update Account</h2>
    </div>

    <form method="POST">
    <div class="form-group">
        <div>    
            <label for="first_name">First Name</label>
            <input type="text" name="first_name" id="first_name" value="<?= htmlspecialchars($instructor['first_name']) ?>" required>
        </div>
        <div>
            <label for="middle_name">Middle Name</label>
            <input type="text" name="middle_name" id="middle_name" value="<?= htmlspecialchars($instructor['middle_name']) ?>">
        </div>
        <div>
            <label for="last_name">Last Name</span></label>
            <input type="text" name="last_name" id="last_name" value="<?= htmlspecialchars($instructor['last_name']) ?>" required>
        </div>
    </div>
    
    <div class="form-group">
        <div>
            <label for="department">Department</span></label>
            <input type="text" name="department" id="department" value="<?= htmlspecialchars($instructor['department']) ?>" required>
        </div>
        <div>
            <label for="position">Position</span></label>
            <input type="text" name="position" id="position" value="<?= htmlspecialchars($instructor['position']) ?>" required>
        </div>
    </div>

    <div class="form-group">
        <div>
            <label for="email">Email</span></label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($instructor['email']) ?>" required>
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