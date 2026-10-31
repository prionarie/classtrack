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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_code = trim($_POST['course_code']);
    $section = trim($_POST['section']);

    $stmt = $con->prepare("SELECT class_id FROM classes WHERE course_code = ? AND section = ?");
    $stmt->bind_param("ss", $course_code, $section);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "Class not found.";
        exit;
    }

    $row = $result->fetch_assoc();
    $class_id = $row['class_id'];

    $check_stmt = $con->prepare("SELECT * FROM student_classes WHERE student_number = ? AND class_id = ?");
    $check_stmt->bind_param("ii", $student_number, $class_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo "You are already registered in this class.";
    } else {
        $insert_stmt = $con->prepare("INSERT INTO student_classes (student_number, class_id) VALUES (?, ?)");
        $insert_stmt->bind_param("ii", $student_number, $class_id);

        if ($insert_stmt->execute()) {
            header("Location: ../lists/classlist.php?signup=success");
            exit();
        } else {
            echo "Error: " . $insert_stmt->error;
        }

        $insert_stmt->close();
    }

    $check_stmt->close();
    $stmt->close();
    $con->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Join Class | [Placeholder] Class Attendance Report System</title>
  <link rel="stylesheet" href="../../../assets/css/addrecord.css">
</head>
<body>

<div class="navbar">
  <img src="../../../assets/imgs/[Placeholder]-logo.png" alt="[Placeholder] Logo">
  <div class="nav-items">
    <a class="nav-item" href="../index.php">🏠 HOME</a>
    <a class="nav-item" href="../lists/classlist.php">📚 CLASSES</a>
    <a class="nav-item" href="../attendance/selectclass.php">🗓️ ATTENDANCE</a>
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
      <h2>Join Class</h2>
    </div>

    <form action="joinclass.php" method="POST">
    
    <div class="form-group">
        <div>    
            <label for="course_title">Course Code</label>
            <input type="text" name="course_code" id="course_code" placeholder="Enter Course Code" required>
        </div>
    </div>
    <div class="form-group">
        <div>
            <label for="course_code">Section</label>
            <input type="text" name="section" id="section" placeholder="Enter Section" required>
        </div>
    </div>

    <div class="form-group">
        <button type="submit">Join</button>
    </div>

    </form>

  </div>
</div>

<script src="../../../assets/js/dropdown.js"></script>

</body>
</html>