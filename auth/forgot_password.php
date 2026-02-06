<?php
session_start();
require_once '../config/db_connection.php';
require_once '../includes/email_helper.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');

    if (empty($email)) {
        $error = 'Please enter your email address';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // Check if email exists
        $query = "SELECT id, name FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);

            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store token in database
            $update_query = "UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?";
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, 'sss', $token, $expiry, $email);

            if (mysqli_stmt_execute($update_stmt)) {
                // Generate reset link
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $reset_link = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;

                // Send email
                if (sendPasswordResetEmail($email, $user['name'], $reset_link)) {
                    $success = 'Password reset instructions have been sent to your email address. Please check your inbox.';
                } else {
                    $error = 'Failed to send email. Please try again later or contact support.';
                }
            } else {
                $error = 'Error processing request. Please try again.';
            }

            mysqli_stmt_close($update_stmt);
        } else {
            // Don't reveal if email exists or not for security
            $success = 'If an account exists with this email, you will receive password reset instructions.';
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
    <title>Forgot Password - CropManage</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="logo-section">
                <h1>CropManage</h1>
                <p class="subtitle">Reset Your Password</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (!$success): ?>
                <form method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required placeholder="Enter your registered email">
                        <small style="color: #666; font-size: 0.85rem; margin-top: 5px; display: block;">
                            We'll send you a link to reset your password
                        </small>
                    </div>

                    <button type="submit" class="btn btn-primary">Send Reset Link</button>
                </form>
            <?php endif; ?>

            <div class="auth-footer">
                <p>Remember your password? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>
</body>

</html>