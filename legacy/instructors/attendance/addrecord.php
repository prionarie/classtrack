<?php
session_start();
include '../../config/dbcon.php';

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

$stmt = $con->prepare("
    SELECT c.class_id, c.course_code, c.course_title, c.section
    FROM classes c
    JOIN instructor_classes sc ON c.class_id = sc.class_id
    WHERE sc.employee_id = ?
    ORDER BY c.course_code ASC
");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$classes = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = $_POST['class_id'];
    $date = $_POST['date'];

    if (empty($class_id)) {
        echo "Class is required.";
        exit;
    }
    $stmt = $con->prepare("SELECT record_id FROM attendance_records WHERE class_id = ? AND date = ?");
    $stmt->bind_param("is", $class_id, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $record_id = $result->fetch_assoc()['record_id'];
    } else {
        $stmt = $con->prepare("INSERT INTO attendance_records (class_id, date) VALUES (?, ?)");
        $stmt->bind_param("is", $class_id, $date);

        if (!$stmt->execute()) {
            echo "Error creating attendance record!";
            exit;
        }
        $record_id = $stmt->insert_id;
    }
    $stmt->close();
    $result->free();

    header("Location: newrecord.php?record_id=" . $record_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Attendance Record | School Class Attendance Report System</title>
  <link rel="stylesheet" href="../../assets/css/addrecord.css">
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
        <a href="../account/viewaccount.php">✏️ View Account</a>
        <a href="../account/editpassword.php">🔐 Change Password</a>
        <a href="../account/logout.php" style="color: red;">🚪 Log Out</a>
      </div>
  </div>
</div>

<a href="../lists/attendancelist.php" class="go-back">Go Back</a>

<div class="page-container">
<div class="container-box">

    <div class="container-header">
      <h2>Create Attendance Record</h2>
    </div>

    <form action="addrecord.php" method="POST">
    
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
        <div>
            <label for="date">Date</label>
            <input type="date" name="date" id="date" required>
        </div>
    </div>

    <div class="form-group">
        <button type="submit">Create Record</button>
    </div>

    </form>

  </div>
</div>

<script src="../../assets/js/dropdown.js"></script>

</body>
</html>