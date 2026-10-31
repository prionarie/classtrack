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

if (!isset($_GET['report_id'])) {
    die("No report ID provided.");
}

$report_id = $_GET['report_id'];

$stmt = $con->prepare("SELECT ar.*, c.course_code, c.section, r.date 
                       FROM absence_reports ar
                       JOIN classes c ON ar.class_id = c.class_id
                       JOIN attendance_records r ON ar.record_id = r.record_id
                       WHERE ar.report_id = ? AND ar.student_number = ?");
$stmt->bind_param("ii", $report_id, $student_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Report cannot be found or edited.");
}

$message = "";
$report = $result->fetch_assoc();
$remarks = $report['remarks'] ?? 'Pending';
$feedback = trim($report['feedback'] ?? '');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $reason = $_POST['reason'];
    $file_path = $report['file_upload'];

    if (!empty($_FILES['file_upload']['name'])) {
        $upload_dir = "../../../assets/uploads/";
        $file_name = basename($_FILES['file_upload']['name']);
        $sanitized_name = preg_replace("/[^A-Za-z0-9_\-\.]/", '_', $file_name);
        $target_path = $upload_dir . time() . "_" . $sanitized_name;
        
        if (move_uploaded_file($_FILES['file_upload']['tmp_name'], $target_path)) {
            if (!empty($report['file_upload']) && file_exists($report['file_upload']) && $report['file_upload'] !== $target_path) {
                unlink($report['file_upload']);
            }
            $file_path = $target_path;
        } else {
            $message = "Failed to upload file.";
        }
    }

    $stmt = $con->prepare("UPDATE absence_reports SET reason = ?, file_upload = ?, remarks = 'Pending' WHERE report_id = ? AND student_number = ?");
    $stmt->bind_param("ssii", $reason, $file_path, $report_id, $student_number);

    if ($stmt->execute()) {
        header("Location: ../lists/reportlist.php");
        exit;
    } else {
        $message = "Failed to update report.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Absence Report | [Placeholder] Class Attendance Report System</title>
  <link rel="stylesheet" href="../../../assets/css/report.css">
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

<div class="top-bar">
  <a href="../lists/reportlist.php" class="go-back">Go Back</a>
</div>

<div class="page-container">
  <div class="container-box">
    <div class="container-header">
      <h2>Edit Absence Report</h2>
    </div>

    <?php if (!empty($message)): ?>
      <div class="form-message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

      <div class="form-group">
        <label>Course Code</label>
        <input type="text" value="<?= htmlspecialchars($report['course_code']) ?>" readonly>
      </div>

      <div class="form-group">
        <label>Section</label>
        <input type="text" value="<?= htmlspecialchars($report['section']) ?>" readonly>
      </div>

      <div class="form-group">
        <label>Date of Absence</label>
        <input type="date" value="<?= htmlspecialchars($report['date']) ?>" readonly>
      </div>

      <div class="form-group">
        <label for="reason">Reason</label>
        <textarea name="reason" id="reason" rows="5" required><?= htmlspecialchars($report['reason']) ?></textarea>
      </div>

      <div class="form-group">
        <label for="file_upload">Upload New Supporting Document (Optional)</label>
        <input type="file" name="file_upload" id="file_upload" accept=".jpg,.jpeg,.png,.pdf,.docx">
        <?php if (!empty($report['file_upload'])): ?>
          <p>Current Document: <a href="<?= htmlspecialchars($report['file_upload']) ?>" target="_blank">View Uploaded File</a></p>
        <?php endif; ?>
      </div>
      
      <div class="form-group">
        <div class="instructor-actions">
          <label>Instructor Remarks: 
          <span class="<?php
            if ($remarks === 'Validated') echo 'status-validated';
            elseif ($remarks === 'Rejected') echo 'status-rejected';
            else echo 'status-pending';?>">
            <?= htmlspecialchars($remarks) ?>
          </span>
          </label>
        </div>
      <?php if (!empty($feedback)): ?>
        <div class="instructor-actions">
          <label>Instructor Feedback</label>
          <textarea rows="4" readonly class="readonly-feedback"><?= htmlspecialchars($feedback) ?></textarea>
        </div>
      <?php endif; ?>
      </div>

      <div class="form-group">
        <button type="submit" class="submit">Resubmit</button>
      </div>
    </form>
  </div>
</div>

<script src="../../../assets/js/dropdown.js"></script>

</body>
</html>