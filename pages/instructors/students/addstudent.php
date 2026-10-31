<?php
include "../../../assets/db/dbcon.php";
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_number = $_POST['student_number'];

    $check_stmt = $con->prepare("SELECT * FROM student_classes WHERE student_number = ? AND class_id = ?");
    $check_stmt->bind_param("ii", $student_number, $class_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo "This student is already registered to this class.";
    } else {
        $insert_stmt = $con->prepare("INSERT INTO student_classes (student_number, class_id) VALUES (?, ?)");
        $insert_stmt->bind_param("ii", $student_number, $class_id);

        if ($insert_stmt->execute()) {
            header("Location: ../lists/studentlist.php?class_id=" . urlencode($class_id) . "&signup=success");
            exit();
        } else {
            echo "Error: " . $insert_stmt->error;
        }

        $insert_stmt->close();
    }

    $check_stmt->close();
    $con->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Student to Class | [Placeholder] Class Attendance Report System</title>
  <link rel="stylesheet" href="../../../assets/css/addrecord.css">
</head>
<body>

<div class="navbar">
  <img src="../../../assets/imgs/[Placeholder]-logo.png" alt="[Placeholder] Logo">
  <div class="nav-items">
    <a class="nav-item" href="../index.php">🏠 HOME</a>
    <a class="nav-item" href="../lists/classlist.php">📚 CLASSES</a>
    <a class="nav-item" href="../lists/attendancelist.php">🗓️ ATTENDANCE</a>
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

<a href="../lists/studentlist.php?class_id=<?= urlencode($class_id) ?>" class="go-back">Go Back</a>

<div class="page-container">
  <div class="container-box">

    <div class="container-header">
      <h2>Add Student to Class</h2>
    </div>

    <form action="addstudent.php?class_id=<?= htmlspecialchars($class_id) ?>" method="POST">
    
    <div class="form-group">
        <div>
            <label for="student_number">Enter Student Number</label>
            <input type="text" name="student_number" id="student_number" placeholder="Enter Student Number" required>
        </div>
    </div>

    <div class="form-group">
        <button type="submit">Register Student</button>
    </div>

    </form>

  </div>
</div>

<script src="../../../assets/js/dropdown.js"></script>

</body>
</html>