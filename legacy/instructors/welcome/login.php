<?php
session_start();
include "../../config/dbcon.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $con->prepare("SELECT * FROM instructors WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['employee_id'] = $user['employee_id'];
            $_SESSION['email'] = $user['email'];
            
            header("Location: ../index.php");
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "User not found.";
    }

    $stmt->close();
    $con->close();
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Instructor Login | School Class Attendance Report System</title>
  <link rel="stylesheet" href="../../assets/css/login.css">
</head>
<body>
  <div class="background">
    <img src="../../assets/imgs/login-bg.png" alt="Background">
  </div>
  <div class="login-box">
    <div class="login-header">
      <img src="../../assets/imgs/school-logo.png" alt="School Logo" class="logo">
      <h2>WELCOME TO SCHOOL CLASS<br>ATTENDANCE REPORT SYSTEM</h2>
    </div>

    <form action="login.php" method="POST">
      <label for="email">EMAIL</label>
      <input type="email" id="email" name="email" placeholder="Enter your email" required>

      <label for="password">PASSWORD</label>
      <input type="password" id="password" name="password" placeholder="Enter your password" required>

      <button type="submit">LOG IN</button>
    </form>

    <p class="signup"><a href="signup.php" class="signup-link">Not Registered? Sign Up</a></p>
  </div>
</body>
</html>