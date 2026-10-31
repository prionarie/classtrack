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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_title = $_POST['course_title'];
    $course_code  = $_POST['course_code'];
    $section      = $_POST['section'];
    $employee_id  = $_SESSION['employee_id'];

    $schedule_entries = [];

    $class_days  = $_POST['class_day'] ?? [];
    $start_times = $_POST['start_time'] ?? [];
    $end_times   = $_POST['end_time'] ?? [];

    for ($i = 0; $i < count($class_days); $i++) {
        $formatted_start = date("h:i a", strtotime($start_times[$i]));
        $formatted_end   = date("h:i a", strtotime($end_times[$i]));
        $schedule_entries[] = "{$class_days[$i]} {$formatted_start}-{$formatted_end}";
    }

    $class_schedule = implode(", ", $schedule_entries);

    $stmt = $con->prepare("INSERT INTO classes (course_title, course_code, section, class_schedule) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $course_title, $course_code, $section, $class_schedule);

    if ($stmt->execute()) {
        $class_id = $con->insert_id;

        $link_stmt = $con->prepare("INSERT INTO instructor_classes (employee_id, class_id) VALUES (?, ?)");
        $link_stmt->bind_param("ii", $employee_id, $class_id);
        $link_stmt->execute();
        $link_stmt->close();

        header("Location: ../lists/classlist.php?add=success");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $con->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Class Registration | [Placeholder] Class Attendance Report System</title>
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

<a href="../lists/classlist.php" class="go-back">Go Back</a>

<div class="page-container">
    <div class="container-box-big">
        <div class="container-header">
            <h2>Class Registration</h2>
        </div>
    <form action="addclass.php" method="POST">
    <div class="form-group">
        <div>    
            <label for="course_title">Course Title</label>
            <input type="text" name="course_title" id="course_title">
        </div>
        <div>
            <label for="course_code">Course Code<span class="required">*</span></label>
            <input type="text" name="course_code" id="course_code" required>
        </div>
    </div>  
    <div class="form-group">
        <div>
            <label for="section">Section<span class="required">*</span></label>
            <input type="text" name="section" id="section" required>
        </div>
    </div>
    <label>Class Schedule (Day/Start Time/End Time)</label>
    <div id="schedule-container">
    <div class="form-group">
        <div>
            <select name="class_day[]">
                <option value="" disabled selected>Select Day</option>
                <option value="Monday">Monday</option>
                <option value="Tuesday">Tuesday</option>
                <option value="Wednesday">Wednesday</option>
                <option value="Thursday">Thursday</option>
                <option value="Friday">Friday</option>
                <option value="Saturday">Saturday</option>
            </select>
        </div>
        <div>
            <input type="time" name="start_time[]">
        </div>
        <div>
            <input type="time" name="end_time[]">
        </div>
        <div>
        <button type="button" class="add-btn">+</button>
        <button type="button" class="delete-btn">-</button>
        </div>
    </div>
    </div>

    <div class="form-group">
        <button type="submit">Register Class</button>
    </div>

    </form>

  </div>
</div>

<script src="../../../assets/js/class_schedule.js"></script>

</body>
</html>