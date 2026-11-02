<?php
include "../../config/dbcon.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_number = $_POST['student_number'];
    $student_type   = $_POST['student_type'];
    $first_name     = $_POST['first_name'];
    $middle_name    = $_POST['middle_name'];
    $last_name      = $_POST['last_name'];
    $section        = $_POST['section'];
    $email          = $_POST['email'];
    $password       = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        die("Passwords do not match.");
    }
    if (strlen($password) < 6) {
        die("Password must be at least 6 characters.");
    }
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $checkStmt = $con->prepare("SELECT * FROM students WHERE student_number = ? OR email = ?");
    $checkStmt->bind_param("is", $student_number, $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $error = "Student Number or Email already exists.";
    } else {
        $stmt = $con->prepare("INSERT INTO students 
            (student_number, student_type, first_name, middle_name, last_name, section, email, password) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "isssssss",
            $student_number,
            $student_type,
            $first_name,
            $middle_name,
            $last_name,
            $section,
            $email,
            $hashed_password
        );

        if ($stmt->execute()) {
            header("Location: login.php?signup=success");
            exit();
        } else {
            $error = "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    $checkStmt->close();
    $con->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Sign Up | School Class Attendance Report System</title>
  <link rel="stylesheet" href="../../assets/css/signup.css">
</head>
<body>

  <div class="background">
    <img src="../../assets/imgs/login-bg.png" alt="Background">
  </div>

  <div class="login-box">
    <div class="login-header">
      <h2>STUDENT SIGN UP</h2>
    </div>

    <form action="signup.php" method="POST">
    <div class="form-group">
        <div>
            <label for="student_number">Student Number <span class="required">*</span></label>
            <input type="text" name="student_number" id="student_number" required>
        </div>
    </div>
    
    <div class="form-group">
        <div>    
            <label for="first_name">First Name <span class="required">*</span></label>
            <input type="text" name="first_name" id="first_name" required>
        </div>
        <div>
            <label for="middle_name">Middle Name</label>
            <input type="text" name="middle_name" id="middle_name">
        </div>
        <div>
            <label for="last_name">Last Name <span class="required">*</span></label>
            <input type="text" name="last_name" id="last_name" required>
        </div>
    </div>
    
    <div class="form-group">
        <div>
            <label for="student_type">Student Type <span class="required">*</span></label>
            <select name="student_type" id="student_type" required>
                <option value="regular">Regular</option>
                <option value="irregular">Irregular</option>
            </select>
        </div>
        <div>
            <label for="section">Section</label>
            <input type="text" name="section" id="section">
        </div>
    </div>

    <div class="form-group">
        <div>
            <label for="email">Email Address <span class="required">*</span></label>
            <input type="email" name="email" id="email" required>
        </div>
    </div>
    <div class="form-group">
        <div>
            <label for="password">Password <span class="required">*</span></label>
            <input type="password" id="password" name="password" required minlength="6">
        </div>
        <div>
            <label for="confirm_password">Confirm Password <span class="required">*</span></label>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
        </div>
    </div>

    <div class="form-group">
        <button type="submit">Sign Up</button>
    </div>
    </form>

    <p class="signup"><a href="login.php" class="go-back">Go Back</a></p>
  </div>

</body>
</html>