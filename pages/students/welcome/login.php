<?php
session_start();
include "../../../assets/db/dbcon.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST['student_number']) && isset($_POST['password'])) {
    $student_number = $_POST['student_number'];
    $password = $_POST['password'];

    $stmt = $con->prepare("SELECT * FROM students WHERE student_number = ?");
    $stmt->bind_param("s", $student_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['student_number'] = $user['student_number'];
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
  <title>Student Login | [Placeholder] Class Attendance Report System</title>
  <link rel="stylesheet" href="../../../assets/css/login.css">
</head>
<body>
  <div class="background">
    <img src="../../../assets/imgs/login-bg.png" alt="Background">
  </div>
  <div class="login-box">
    <div class="login-header">
      <img src="../../../assets/imgs/[Placeholder]-logo.png" alt="[Placeholder] Logo" class="logo">
      <h2>WELCOME TO [Placeholder] CLASS<br>ATTENDANCE REPORT SYSTEM</h2>
    </div>

    <form action="login.php" method="POST">
      <label for="student_number">STUDENT NUMBER</label>
      <input type="student_number" id="student_number" name="student_number" placeholder="Enter your student number" required>

      <label for="password">PASSWORD</label>
      <input type="password" id="password" name="password" placeholder="Enter your password" required>

      <button type="submit">LOG IN</button>
    </form>

    <p class="signup"><a href="signup.php" class="signup-link">Not Registered? Sign Up</a></p>
  </div>
</body>
</html>