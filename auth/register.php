<?php
session_start();
require_once '../config/db_connection.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';
  $name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
  $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
  $address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');

  // Validation
  if (empty($email) || empty($password) || empty($confirm_password) || empty($name)) {
    $error = 'Please fill in all required fields';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Please enter a valid email address';
  } elseif (!preg_match('/^[a-zA-Z\s]{2,50}$/', $name)) {
    $error = 'Name should only contain letters and spaces (2-50 characters)';
  } elseif (!empty($phone) && !preg_match('/^[0-9]{10}$/', $phone)) {
    $error = 'Phone number must be exactly 10 digits';
  } elseif (strlen($password) < 6) {
    $error = 'Password must be at least 6 characters long';
  } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$/', $password)) {
    $error = 'Password must contain at least one uppercase letter, one lowercase letter, and one number';
  } elseif ($password !== $confirm_password) {
    $error = 'Passwords do not match';
  } else {
    $check_query = "SELECT id FROM users WHERE email = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, 's', $email);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($check_result) > 0) {
      $error = 'Email already registered';
    } else {
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      $insert_query = "INSERT INTO users (name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)";
      $insert_stmt = mysqli_prepare($conn, $insert_query);
      mysqli_stmt_bind_param($insert_stmt, 'sssss', $name, $email, $hashed_password, $phone, $address);

      if (mysqli_stmt_execute($insert_stmt)) {
        header('Location: login.php?success=1');
        exit;
      } else {
        $error = 'Registration failed. Please try again.';
      }
      mysqli_stmt_close($insert_stmt);
    }
    mysqli_stmt_close($check_stmt);
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - CropManage</title>
  <link rel="stylesheet" href="../assets/style.css">
  <style>
    .password-wrapper {
      position: relative;
      display: flex;
      align-items: center;
    }

    .password-wrapper input {
      width: 100%;
      padding-right: 45px;
    }

    .password-toggle-btn {
      position: absolute;
      right: 12px;
      background: none;
      border: none;
      cursor: pointer;
      padding: 5px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #666;
      transition: color 0.3s ease;
    }

    .password-toggle-btn:hover {
      color: #2d5016;
    }

    .password-toggle-btn svg {
      width: 20px;
      height: 20px;
    }

    .password-requirements {
      font-size: 0.75rem;
      color: #666;
      margin-top: 0.25rem;
    }

    .password-requirements.error {
      color: #c62828;
    }

    .password-requirements.success {
      color: #4caf50;
    }
  </style>
</head>

<body class="auth-page">
  <div class="auth-container">
    <div class="auth-card">
      <h1>CropManage</h1>
      <p class="subtitle">Create Your Account</p>

      <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form method="POST" class="auth-form" id="registerForm">
        <div class="form-group">
          <label for="name">Full Name *</label>
          <input type="text" id="name" name="name" required placeholder="Enter your full name" pattern="[a-zA-Z\s]{2,50}" title="Name should only contain letters and spaces (2-50 characters)">
        </div>

        <div class="form-group">
          <label for="email">Email Address *</label>
          <input type="email" id="email" name="email" required placeholder="Enter your email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Please enter a valid email address">
        </div>

        <div class="form-group">
          <label for="phone">Phone Number</label>
          <input type="tel" id="phone" name="phone" placeholder="Enter your 10-digit phone number" pattern="[0-9]{10}" title="Phone number must be exactly 10 digits" maxlength="10">
          <small style="color: #666; font-size: 0.85rem;">Optional: 10 digits only</small>
        </div>

        <div class="form-group">
          <label for="address">Address</label>
          <input type="text" id="address" name="address" placeholder="Enter your address">
        </div>

        <div class="form-group">
          <label for="password">Password *</label>
          <input type="password" id="password" name="password" required placeholder="At least 6 characters">
          <small class="password-requirements">Must contain: uppercase, lowercase, and number (min 6 chars)</small>
        </div>

        <div class="form-group">
          <label for="confirm_password">Confirm Password *</label>
          <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm your password">
        </div>

        <button type="submit" class="btn btn-primary">Register</button>
      </form>

      <div class="auth-footer">
        <p>Already have an account? <a href="login.php">Login here</a></p>
      </div>
    </div>
  </div>

  <script>
    // Real-time password validation
    document.getElementById('password').addEventListener('input', function() {
      const password = this.value;
      const requirements = document.querySelector('.password-requirements');

      const hasUppercase = /[A-Z]/.test(password);
      const hasLowercase = /[a-z]/.test(password);
      const hasNumber = /\d/.test(password);
      const hasMinLength = password.length >= 6;

      if (password.length === 0) {
        requirements.className = 'password-requirements';
        requirements.textContent = 'Must contain: uppercase, lowercase, and number (min 6 chars)';
      } else if (hasUppercase && hasLowercase && hasNumber && hasMinLength) {
        requirements.className = 'password-requirements success';
        requirements.textContent = '✓ Password is strong';
      } else {
        requirements.className = 'password-requirements error';
        let missing = [];
        if (!hasUppercase) missing.push('uppercase');
        if (!hasLowercase) missing.push('lowercase');
        if (!hasNumber) missing.push('number');
        if (!hasMinLength) missing.push('6+ chars');
        requirements.textContent = 'Missing: ' + missing.join(', ');
      }
    });

    // Confirm password match validation
    document.getElementById('confirm_password').addEventListener('input', function() {
      const password = document.getElementById('password').value;
      const confirmPassword = this.value;

      if (confirmPassword.length > 0) {
        if (password === confirmPassword) {
          this.style.borderColor = '#4caf50';
        } else {
          this.style.borderColor = '#f44336';
        }
      } else {
        this.style.borderColor = '';
      }
    });

    // Phone number - only digits
    document.getElementById('phone').addEventListener('input', function() {
      this.value = this.value.replace(/[^0-9]/g, '');
    });
  </script>
</body>

</html>