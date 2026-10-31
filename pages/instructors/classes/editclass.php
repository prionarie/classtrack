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

if (!$class_id) {
    die("Class ID is required.");
}

$stmt = $con->prepare("SELECT * FROM classes WHERE class_id = ?");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$result = $stmt->get_result();
$class = $result->fetch_assoc();

if (!$class) {
    die("Class not found.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_title = $_POST['course_title'];
    $course_code = $_POST['course_code'];
    $section = $_POST['section'];

    $schedule_entries = [];
    $class_days = $_POST['class_day'] ?? [];
    $start_times = $_POST['start_time'] ?? [];
    $end_times = $_POST['end_time'] ?? [];

    for ($i = 0; $i < count($class_days); $i++) {
        $formatted_start = date("h:i a", strtotime($start_times[$i]));
        $formatted_end = date("h:i a", strtotime($end_times[$i]));
        $schedule_entries[] = "{$class_days[$i]} {$formatted_start}-{$formatted_end}";
    }

    $class_schedule = implode(", ", $schedule_entries);

    $update_stmt = $con->prepare("UPDATE classes SET course_title = ?, course_code = ?, section = ?, class_schedule = ? WHERE class_id = ?");
    $update_stmt->bind_param("sssss", $course_title, $course_code, $section, $class_schedule, $class_id);

    if ($update_stmt->execute()) {
        header("Location: ../lists/classlist.php?edit=success");
        exit();
    } else {
        echo "Error: " . $update_stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Class Details | [Placeholder] Class Attendance Report System</title>
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
    <h2>Edit Class Details</h2>
  </div>

  <form action="" method="POST">
    <div class="form-group">
      <div>
        <label for="course_title">Course Title</label>
        <input type="text" name="course_title" id="course_title" value="<?= htmlspecialchars($class['course_title']) ?>" required>
      </div>
      <div>
        <label for="course_code">Course Code</label>
        <input type="text" name="course_code" id="course_code" value="<?= htmlspecialchars($class['course_code']) ?>" required>
      </div>
    </div>

    <div class="form-group">
      <div>
        <label for="section">Section</label>
        <input type="text" name="section" id="section" value="<?= htmlspecialchars($class['section']) ?>" required>
      </div>
    </div>

    <label>Class Schedule (Day/Start Time/End Time)</label>
    <div id="schedule-container">
      <?php
      $schedule_rows = explode(",", $class['class_schedule']);
      foreach ($schedule_rows as $index => $entry) {
        if (preg_match('/^(\w+)\s+(\d{1,2}:\d{2}\s(?:am|pm))-(\d{1,2}:\d{2}\s(?:am|pm))$/i', trim($entry), $matches)) {
          $day = $matches[1];
          $start_time = date("H:i", strtotime($matches[2]));
          $end_time = date("H:i", strtotime($matches[3]));
        } else {
          $day = $start_time = $end_time = '';
        }
      ?>
    <div class="form-group schedule-row">
        <div>
            <select name="class_day[]" required>
                <option value="" disabled>Select Day</option>
                <?php
                $days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
                foreach ($days as $d) {
                    $selected = ($d == $day) ? "selected" : "";
                    echo "<option value=\"$d\" $selected>$d</option>";
                }
                ?>
            </select>
        </div>
        <div><input type="time" name="start_time[]" value="<?= $start_time ?>" required></div>
        <div><input type="time" name="end_time[]" value="<?= $end_time ?>" required></div>
        <div>
            <button type="button" class="add-btn">+</button>
            <button type="button" class="delete-btn">-</button>
        </div>
    </div>
    <?php } ?>
    </div>

    <div class="form-group">
      <button type="submit">Save</button>
    </div>

  </form>

</div>
</div>

<script src="../../../assets/js/class_schedule.js"></script>

</body>
</html>