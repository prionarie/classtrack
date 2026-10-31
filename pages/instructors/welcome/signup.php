<?php
include "../../../assets/db/dbcon.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employee_id    = $_POST['employee_id'];
    $first_name     = $_POST['first_name'];
    $middle_name    = $_POST['middle_name'];
    $last_name      = $_POST['last_name'];
    $department     = $_POST['department'];
    $position       = $_POST['position'];
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

    $checkStmt = $con->prepare("SELECT * FROM instructors WHERE employee_id = ? OR email = ?");
    $checkStmt->bind_param("is", $employee_id, $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        echo "Employee ID or Email already exists.";
        exit();
    }
    $checkStmt->close();

    $stmt = $con->prepare("INSERT INTO instructors 
        (employee_id, first_name, middle_name, last_name, department, position, email, password) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "isssssss",
        $employee_id,
        $first_name,
        $middle_name,
        $last_name,
        $department,
        $position,
        $email,
        $hashed_password
    );

    if ($stmt->execute()) {
        header("Location: login.php?signup=success");
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
  <title>Instructor Sign Up | [Placeholder] Class Attendance Report System</title>
  <link rel="stylesheet" href="../../../assets/css/signup.css">
</head>
<body>

  <div class="background">
    <img src="../../../assets/imgs/login-bg.png" alt="Background">
  </div>

  <div class="login-box">
    <div class="login-header">
      <h2>Instructor Sign Up</h2>
    </div>

    <form action="signup.php" method="POST">
    
    <div class="form-group">
        <div>
            <label for="employee_id">Employee ID <span class="required">*</span></label>
            <input type="text" name="employee_id" id="employee_id" required>
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
            <label for="department">Department <span class="required">*</span></label>
            <input type="text" name="department" id="department" required>
        </div>
        <div>
            <label for="position">Position <span class="required">*</span></label>
            <input type="text" name="position" id="position" required>
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