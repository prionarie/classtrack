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

$search = $_GET['search'] ?? '';
$searchParam = "%$search%";

if (!empty($search)) {
  $stmt = $con->prepare("
      SELECT c.class_id, c.course_title, c.course_code, c.section, c.class_schedule 
      FROM classes c
      INNER JOIN instructor_classes ic ON c.class_id = ic.class_id
      WHERE ic.employee_id = ?
        AND (c.class_id LIKE ? OR c.course_code LIKE ? OR c.section LIKE ? OR c.course_title LIKE ?)
      ORDER BY c.class_id ASC
  ");
  $stmt->bind_param("sssss", $employee_id, $searchParam, $searchParam, $searchParam, $searchParam);
} else {
  $stmt = $con->prepare("
      SELECT c.class_id, c.course_title, c.course_code, c.section, c.class_schedule 
      FROM classes c
      INNER JOIN instructor_classes ic ON c.class_id = ic.class_id
      WHERE ic.employee_id = ?
      ORDER BY c.class_id ASC
  ");
  $stmt->bind_param("s", $employee_id);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Class Information List | [Placeholder] Class Attendance Report System</title>
  <link rel="stylesheet" href="../../../assets/css/list.css">
</head>
<body>

<div class="navbar">
  <img src="../../../assets/imgs/[Placeholder]-logo.png" alt="[Placeholder] Logo">
  <div class="nav-items">
    <a class="nav-item" href="../index.php">🏠 HOME</a>
    <a class="nav-item" href="classlist.php">📚 CLASSES</a>
    <a class="nav-item" href="attendancelist.php">🗓️ ATTENDANCE</a>
    <a class="nav-item" href="reportlist.php">📁 REPORTS</a>
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

<div class="top-bar">
    <a href="../index.php" class="go-back">Go Back</a>
    <form method="GET" style="display: flex; align-items: center;">
      <input type="text" name="search" class="search-bar" placeholder="🔍 Search Classes"
      value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
      <button type="submit" class="search-button">Search</button>
    </form>
</div>

<div class="page-container">
  <div class="container-box">
    <div class="container-header">
      <h2>Class Information List</h2>
    </div>

    <div>
      <button class="btn add" onclick="window.location.href='../classes/addclass.php'">+ Add Class</button>
    </div>

  </div>
    <table>
        <tr>
            <th>Class ID</th>
            <th>Course Title</th>
            <th>Course Code</th>
            <th>Section</th>
            <th>Class Schedule</th>
            <th>Student List</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['class_id']) ?></td>
                <td><?= htmlspecialchars($row['course_title']) ?></td>
                <td><?= htmlspecialchars($row['course_code']) ?></td>
                <td><?= htmlspecialchars($row['section']) ?></td>
                <td><?= htmlspecialchars($row['class_schedule']) ?></td>
                <td>
                    <button onclick="window.location.href='../lists/studentlist.php?class_id=<?= htmlspecialchars($row['class_id']) ?>'">View</button>
                </td>
                <td>
                    <button class="btn edit" onclick="window.location.href='../classes/editclass.php?class_id=<?= htmlspecialchars($row['class_id']) ?>'">Edit</button>
                    <form action="../classes/deleteclass.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this class?');">
                      <input type="hidden" name="class_id" value="<?= htmlspecialchars($row['class_id']) ?>">
                      <button class="btn delete" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<script src="../../../assets/js/dropdown.js"></script>

</body>
</html>