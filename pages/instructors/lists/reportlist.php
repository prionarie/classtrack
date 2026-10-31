<?php
include "../../../assets/db/dbcon.php";
session_start();

// Ensure $con (mysqli connection) is defined. If the included file did not
// provide it, attempt a sensible default fallback. Adjust credentials as
// needed for your environment.
if (!isset($con) || !($con instanceof mysqli)) {
  $con = new mysqli('localhost', 'root', '', 'classtrack');
  if ($con->connect_errno) {
    die('Database connection failed: ' . $con->connect_error);
  }
}

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
$filter = $_GET['filter'] ?? '';
$searchParam = "%$search%";

$query = "
    SELECT ar.report_id, c.course_code, c.section, s.last_name, s.first_name, s.middle_name, r.date, ar.remarks
    FROM absence_reports ar
    INNER JOIN students s ON ar.student_number = s.student_number
    INNER JOIN classes c ON ar.class_id = c.class_id
    INNER JOIN instructor_classes ic ON ic.class_id = c.class_id
    INNER JOIN attendance_records r ON ar.record_id = r.record_id
    WHERE ic.employee_id = ?
";

$params = [$employee_id];
$types = "i";

if (!empty($search)) {
    $query .= " AND (
        c.course_code LIKE ? OR
        c.section LIKE ? OR
        s.last_name LIKE ? OR
        s.first_name LIKE ? OR
        s.middle_name LIKE ?
    )";
    $params = array_merge($params, array_fill(0, 5, $searchParam));
    $types .= "sssss";
}

if (in_array($filter, ['Pending', 'Validated', 'Rejected'])) {
    $query .= " AND ar.remarks = ?";
    $params[] = $filter;
    $types .= "s";
}

$query .= " ORDER BY r.date DESC";

$stmt = $con->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Absence Report List | [Placeholder] Class Attendance Report System</title>
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
      <input type="text" name="search" class="search-bar" placeholder="🔍 Search Reports"
      value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
      <button type="submit" class="search-button">Search</button>
    </form>
</div>
<div class="page-container">
  <div class="container-box">
    <div class="container-header">
      <h2>Submitted Absence Reports</h2>
    </div>
    <div class="filter-status">
      <?php $currentFilter = $_GET['filter'] ?? ''; ?>
      <label><input type="radio" name="filter" value="" <?= $currentFilter === '' ? 'checked' : '' ?>><strong>All</strong></label>
      <label class="status-pending"><input type="radio" name="filter" value="Pending" <?= $currentFilter === 'Pending' ? 'checked' : '' ?>> Pending</label>
      <label class="status-validated"><input type="radio" name="filter" value="Validated" <?= $currentFilter === 'Validated' ? 'checked' : '' ?>> Validated</label>
      <label class="status-rejected"><input type="radio" name="filter" value="Rejected" <?= $currentFilter === 'Rejected' ? 'checked' : '' ?>> Rejected</label>
      <script>
      document.querySelectorAll('input[name="filter"]').forEach(radio => {
        radio.addEventListener('change', () => {
          const selected = document.querySelector('input[name="filter"]:checked').value;
          const url = new URL(window.location.href);
          if (selected) {
            url.searchParams.set('filter', selected);
          } else {
            url.searchParams.delete('filter');
          }
          window.location.href = url.toString();
        });
      });
      </script>
      </div>
  <table>
    <tr>
      <th>Report ID</th>
      <th>Course Code</th>
      <th>Section</th>
      <th>Last Name</th>
      <th>First Name</th>
      <th>Middle Name</th>
      <th>Date</th>
      <th>Remarks</th>
      <th>Actions</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['report_id']) ?></td>
        <td><?= htmlspecialchars($row['course_code']) ?></td>
        <td><?= htmlspecialchars($row['section']) ?></td>
        <td><?= htmlspecialchars($row['last_name']) ?></td>
        <td><?= htmlspecialchars($row['first_name']) ?></td>
        <td><?= htmlspecialchars($row['middle_name']) ?></td>
        <td><?= htmlspecialchars($row['date']) ?></td>
        <?php
        $remarks = $row['remarks'] ?? '';
        if ($remarks === 'Validated') {
          $remarkClass = 'status-validated';
        } elseif ($remarks === 'Rejected') {
          $remarkClass = 'status-rejected';
        } else {
          $remarks = 'Pending';
          $remarkClass = 'status-pending';
        }
        ?>
        <td><span class="<?= $remarkClass; ?>"><?= htmlspecialchars($row['remarks'] ?: '—') ?></span></td>
        <td>
          <button onclick="window.location.href='../reports/viewreport.php?report_id=<?= urlencode($row['report_id']) ?>'">View</button>
          <form action="../reports/deletereport.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this report?');">
            <input type="hidden" name="report_id" value="<?= htmlspecialchars($row['report_id']) ?>">
            <button type="submit" class="btn delete">Delete</button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
</div>

<script src="../../../assets/js/dropdown.js"></script>

</body>
</html>