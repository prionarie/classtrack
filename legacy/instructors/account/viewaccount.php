<?php
include "../../config/dbcon.php";
session_start();

if (!isset($_SESSION['employee_id'])) {
  header("Location: ../welcome/login.php");
  exit;
}

$employee_id = ($_SESSION['employee_id']);

$stmt = $con->prepare("SELECT first_name FROM instructors WHERE employee_id = ?");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$user_name = 'Instructor';
if ($row = $result->fetch_assoc()) {
    $user_name = $row['first_name'];
}

$sql = "SELECT * FROM instructors WHERE employee_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
  $instructor = $result->fetch_assoc();
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

<a href="../index.php" class="go-back">Go Back</a>

<div class="page-container">
  <div class="container-box-big">
    <div class="container-header">
      <h2>Account Information</h2>
    </div>
        <div class="form-group">
        <div>
            <label for="employee_id">Employee ID</label>
            <input type="text" name="employee_id" id="employee_id" value="<?php echo htmlspecialchars($instructor['employee_id']); ?>" readonly>
        </div>
    </div>
    
    <div class="form-group">
        <div>    
            <label for="first_name">First Name</label>
            <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($instructor['first_name']); ?>" readonly>
        </div>
        <div>
            <label for="middle_name">Middle Name</label>
            <input type="text" name="middle_name" id="middle_name" value="<?php echo htmlspecialchars($instructor['middle_name']); ?>" readonly>
        </div>
        <div>
            <label for="last_name">Last Name</label>
            <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($instructor['last_name']); ?>" readonly>
        </div>
    </div>
    
    <div class="form-group">
        <div>
            <label for="department">Department</label>
            <input type="text" name="department" id="department" value="<?php echo htmlspecialchars($instructor['department']); ?>" readonly>
        </div>
        <div>
            <label for="position">Position</label>
            <input type="text" name="position" id="position" value="<?php echo htmlspecialchars($instructor['position']); ?>" readonly>
        </div>
    </div>

    <div class="form-group">
        <div>
            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($instructor['email']); ?>" readonly>
        </div>
    </div>
    
    <div class="form-group">
      <button onclick="window.location.href='editaccount.php'">Update Account</button>
      <button onclick="window.location.href='editpassword.php'">Change Password</button>
    </div>
  </form>
</div>
</div>

<script src="../../assets/js/dropdown.js"></script>

</body>
</html>