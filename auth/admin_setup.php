<?php
session_start();
require_once '../config/db_connection.php';

$error = '';
$success = '';

// Check if admin already exists
$check_admin = "SELECT COUNT(*) as admin_count FROM users WHERE is_admin = 1";
$result = mysqli_query($conn, $check_admin);
$row = mysqli_fetch_assoc($result);

if ($row['admin_count'] > 0) {
    // Admin already exists, redirect to login
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
    $setup_key = $_POST['setup_key'] ?? '';

    // Setup key for security (change this to your own secret key)
    $required_setup_key = 'CROPMANAGE_ADMIN_SETUP_2024';

    // Validation
    if (empty($email) || empty($password) || empty($confirm_password) || empty($name) || empty($setup_key)) {
        $error = 'Please fill in all required fields';
    } elseif ($setup_key !== $required_setup_key) {
        $error = 'Invalid setup key. Please contact system administrator.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (!preg_match('/^[a-zA-Z\s]{2,50}$/', $name)) {
        $error = 'Name should only contain letters and spaces (2-50 characters)';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/', $password)) {
        $error = 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Check if email already exists
        $check_query = "SELECT id FROM users WHERE email = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, 's', $email);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);

        if (mysqli_num_rows($check_result) > 0) {
            $error = 'Email already registered';
        } else {
            // Create admin user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_query = "INSERT INTO users (name, email, password, is_admin) VALUES (?, ?, ?, 1)";
            $insert_stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($insert_stmt, 'sss', $name, $email, $hashed_password);

            if (mysqli_stmt_execute($insert_stmt)) {
                $success = 'Admin account created successfully! Redirecting to login...';
                header('refresh:3;url=login.php');
            } else {
                $error = 'Failed to create admin account. Please try again.';
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
    <title>Admin Setup - CropManage</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .setup-notice {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .setup-notice h3 {
            margin-top: 0;
            color: #856404;
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
            <h1>🔐 CropManage</h1>
            <p class="subtitle">Admin Account Setup</p>

            <div class="setup-notice">
                <h3>⚠️ First Time Setup</h3>
                <p>This page is only accessible when no admin account exists. You'll need the setup key to proceed.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" class="auth-form" id="setupForm">
                <div class="form-group">
                    <label for="setup_key">Setup Key *</label>
                    <input type="password" id="setup_key" name="setup_key" required placeholder="Enter setup key">
                    <small style="color: #666; font-size: 0.85rem;">Contact system administrator for the setup key</small>
                </div>

                <div class="form-group">
                    <label for="name">Admin Name *</label>
                    <input type="text" id="name" name="name" required placeholder="Enter admin name" pattern="[a-zA-Z\s]{2,50}" title="Name should only contain letters and spaces (2-50 characters)">
                </div>

                <div class="form-group">
                    <label for="email">Admin Email *</label>
                    <input type="email" id="email" name="email" required placeholder="Enter admin email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Please enter a valid email address">
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required placeholder="At least 8 characters">
                    <small class="password-requirements">Must contain: uppercase, lowercase, number, and special character (min 8 chars)</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm your password">
                </div>

                <button type="submit" class="btn btn-primary">Create Admin Account</button>
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
            const hasSpecial = /[@$!%*?&#]/.test(password);
            const hasMinLength = password.length >= 8;

            if (password.length === 0) {
                requirements.className = 'password-requirements';
                requirements.textContent = 'Must contain: uppercase, lowercase, number, and special character (min 8 chars)';
            } else if (hasUppercase && hasLowercase && hasNumber && hasSpecial && hasMinLength) {
                requirements.className = 'password-requirements success';
                requirements.textContent = '✓ Password is strong';
            } else {
                requirements.className = 'password-requirements error';
                let missing = [];
                if (!hasUppercase) missing.push('uppercase');
                if (!hasLowercase) missing.push('lowercase');
                if (!hasNumber) missing.push('number');
                if (!hasSpecial) missing.push('special char');
                if (!hasMinLength) missing.push('8+ chars');
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
    </script>
</body>

</html>