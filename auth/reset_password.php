<?php
session_start();
require_once '../config/db_connection.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';
$valid_token = false;

if (empty($token)) {
    $error = 'Invalid reset link';
} else {
    // Verify token
    $query = "SELECT id, email, reset_token_expiry FROM users WHERE reset_token = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        // Check if token is expired
        if (strtotime($user['reset_token_expiry']) > time()) {
            $valid_token = true;
        } else {
            $error = 'This reset link has expired. Please request a new one.';
        }
    } else {
        $error = 'Invalid reset link';
    }

    mysqli_stmt_close($stmt);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$/', $password)) {
        $error = 'Password must contain at least one uppercase letter, one lowercase letter, and one number';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Update password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update_query = "UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, 'ss', $hashed_password, $token);

        if (mysqli_stmt_execute($update_stmt)) {
            header('Location: login.php?success=2');
            exit;
        } else {
            $error = 'Error updating password. Please try again.';
        }

        mysqli_stmt_close($update_stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - CropManage</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
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
            <div class="logo-section">
                <h1>CropManage</h1>
                <p class="subtitle">Create New Password</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($valid_token): ?>
                <form method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" id="password" name="password" required placeholder="Enter new password">
                        <small class="password-requirements">Must contain: uppercase, lowercase, and number (min 6 chars)</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm new password">
                    </div>

                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </form>
            <?php else: ?>
                <div style="text-align: center; padding: 20px;">
                    <p style="color: #666; margin-bottom: 20px;">This reset link is invalid or has expired.</p>
                    <a href="forgot_password.php" class="btn btn-primary" style="display: inline-block;">Request New Link</a>
                </div>
            <?php endif; ?>

            <div class="auth-footer">
                <p>Remember your password? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>

    <script>
        // Real-time password validation
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirm_password');

        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
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
        }

        if (confirmInput) {
            confirmInput.addEventListener('input', function() {
                const password = passwordInput.value;
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
        }
    </script>
</body>

</html>