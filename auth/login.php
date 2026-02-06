<?php
session_start();
require_once '../config/db_connection.php';
$error = '';
$success = '';
if (isset($_GET['success']) && $_GET['success'] == 1) {
  $success = 'Registration successful! Please login.';
}
if (isset($_GET['success']) && $_GET['success'] == 2) {
  $success = 'Password reset successful! Please login with your new password.';
}
if (isset($_GET['reset']) && $_GET['reset'] == 1) {
  $success = 'Password reset link has been sent to your email.';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  if (empty($email) || empty($password)) {
    $error = 'Please fill in all fields';
  } else {
    $query = "SELECT id, email, password, name, is_admin FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) === 1) {
      $user = mysqli_fetch_assoc($result);
      if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        // Check if admin and redirect accordingly
        if ($user['is_admin'] == 1) {
          header('Location: ../admin/dashboard.php');
        } else {
          header('Location: ../dashboard/index.php');
        }
        exit;
      } else {
        $error = 'Invalid email or password';
      }
    } else {
      $error = 'Invalid email or password';
    }
    mysqli_stmt_close($stmt);
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - CropManage</title>
  <link rel="stylesheet" href="../assets/style.css">
  <style>
    .forgot-password-link {
      text-align: right;
      margin-top: 10px;
      margin-bottom: 20px;
    }

    .forgot-password-link a {
      color: #2d5016;
      text-decoration: none;
      font-size: 0.9rem;
      font-weight: 500;
      transition: all 0.3s;
    }

    .forgot-password-link a:hover {
      text-decoration: underline;
      color: #4a7c27;
    }
  </style>
</head>

<body class="auth-page">
  <div class="auth-container">
    <div class="auth-card">
      <div class="logo-section">
        <h1>CropManage</h1>
        <p class="subtitle">Manage Your Crops Efficiently</p>
      </div>
      <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
      <?php endif; ?>
      <form method="POST" class="auth-form">
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" required placeholder="Enter your email" autocomplete="email">
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required placeholder="Enter your password" autocomplete="current-password">
        </div>
        <div class="forgot-password-link">
          <a href="forgot_password.php">Forgot Password?</a>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
      </form>
      <div class="auth-footer">
        <p>Don't have an account? <a href="register.php">Register here</a></p>
      </div>
    </div>
  </div>
  <script>
    // Remember email if user clicks "Remember me" (optional enhancement)
    document.addEventListener('DOMContentLoaded', function() {
      const emailInput = document.getElementById('email');
      const savedEmail = localStorage.getItem('rememberedEmail');
      if (savedEmail) {
        emailInput.value = savedEmail;
      }
    });
  </script>
</body>

</html>