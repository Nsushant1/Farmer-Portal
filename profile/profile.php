<?php
$page_title = 'My Profile - CropManage';
$css_path = '../assets/style.css';
$base_path = '../';
require_once '../includes/header.php';

$success = '';
$error = '';

// Fetch user data
$query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['name'] ?? ''));
    $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone'] ?? ''));

    if (empty($name) || empty($email)) {
        $error = 'Name and email are required';
    } else {
        // Check if email already exists for another user
        $check_query = "SELECT id FROM users WHERE email = ? AND id != ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, 'si', $email, $user_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);

        if (mysqli_num_rows($check_result) > 0) {
            $error = 'Email already exists';
        } else {
            $update_query = "UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?";
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, 'sssi', $name, $email, $phone, $user_id);

            if (mysqli_stmt_execute($update_stmt)) {
                $success = 'Profile updated successfully!';
                // Refresh user data
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $user = mysqli_fetch_assoc($result);
            } else {
                $error = 'Error updating profile. Please try again.';
            }
            mysqli_stmt_close($update_stmt);
        }
        mysqli_stmt_close($check_stmt);
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All password fields are required';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters long';
    } else {
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET password = ? WHERE id = ?";
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, 'si', $hashed_password, $user_id);

            if (mysqli_stmt_execute($update_stmt)) {
                $success = 'Password changed successfully!';
            } else {
                $error = 'Error changing password. Please try again.';
            }
            mysqli_stmt_close($update_stmt);
        } else {
            $error = 'Current password is incorrect';
        }
    }
}

require_once '../includes/navbar.php';
?>

<main class="profile-page">
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <h1><?php echo htmlspecialchars($user['name']); ?></h1>
            <p class="user-email"><?php echo htmlspecialchars($user['email']); ?></p>
            <p class="member-since">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="profile-sections">
            <!-- Profile Information Section -->
            <section class="profile-section">
                <h2><i class="fas fa-user"></i> Profile Information</h2>
                <form method="POST" class="profile-form">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($user['name']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($user['email']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>

                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </form>
            </section>

            <!-- Change Password Section -->
            <section class="profile-section">
                <h2><i class="fas fa-lock"></i> Change Password</h2>
                <form method="POST" class="profile-form">
                    <div class="form-group">
                        <label for="current_password">Current Password *</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password *</label>
                        <input type="password" id="new_password" name="new_password" required minlength="6">
                        <small>Must be at least 6 characters long</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                    </div>

                    <button type="submit" name="change_password" class="btn btn-primary">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </form>
            </section>

            <!-- Account Statistics Section -->
            <section class="profile-section">
                <h2><i class="fas fa-chart-bar"></i> Account Statistics</h2>
                <div class="stats-grid">
                    <?php
                    // Get total crops
                    $crops_query = "SELECT COUNT(*) as total FROM crops WHERE user_id = ?";
                    $crops_stmt = mysqli_prepare($conn, $crops_query);
                    mysqli_stmt_bind_param($crops_stmt, 'i', $user_id);
                    mysqli_stmt_execute($crops_stmt);
                    $crops_count = mysqli_fetch_assoc(mysqli_stmt_get_result($crops_stmt))['total'];

                    // Get total sales
                    $sales_query = "SELECT COUNT(*) as total, SUM(total_amount) as total_sales FROM sales WHERE user_id = ?";
                    $sales_stmt = mysqli_prepare($conn, $sales_query);
                    mysqli_stmt_bind_param($sales_stmt, 'i', $user_id);
                    mysqli_stmt_execute($sales_stmt);
                    $sales_data = mysqli_fetch_assoc(mysqli_stmt_get_result($sales_stmt));

                    // Get total expenses
                    $expenses_query = "SELECT COUNT(*) as total, SUM(amount) as total_expenses FROM expenses WHERE user_id = ?";
                    $expenses_stmt = mysqli_prepare($conn, $expenses_query);
                    mysqli_stmt_bind_param($expenses_stmt, 'i', $user_id);
                    mysqli_stmt_execute($expenses_stmt);
                    $expenses_data = mysqli_fetch_assoc(mysqli_stmt_get_result($expenses_stmt));
                    ?>

                    <div class="stat-card">
                        <div class="stat-icon crops">
                            <i class="fas fa-seedling"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $crops_count; ?></h3>
                            <p>Total Crops</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon sales">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $sales_data['total'] ?? 0; ?></h3>
                            <p>Sales Records</p>
                            <small>Rs. <?php echo number_format($sales_data['total_sales'] ?? 0, 2); ?></small>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon expenses">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $expenses_data['total'] ?? 0; ?></h3>
                            <p>Expense Records</p>
                            <small>Rs. <?php echo number_format($expenses_data['total_expenses'] ?? 0, 2); ?></small>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</main>

<style>
    .profile-page {
        background: #f5f7fa;
        min-height: calc(100vh - 80px);
        padding: 40px 20px;
    }

    .profile-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .profile-header {
        background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        color: white;
        padding: 40px;
        border-radius: 12px;
        text-align: center;
        margin-bottom: 30px;
        box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
    }

    .profile-avatar {
        margin-bottom: 20px;
    }

    .profile-avatar i {
        font-size: 5rem;
        color: white;
        background: rgba(255, 255, 255, 0.2);
        padding: 20px;
        border-radius: 50%;
    }

    .profile-header h1 {
        margin: 0 0 10px 0;
        font-size: 2rem;
    }

    .user-email {
        font-size: 1.1rem;
        opacity: 0.9;
        margin: 5px 0;
    }

    .member-since {
        font-size: 0.9rem;
        opacity: 0.8;
        margin: 10px 0 0 0;
    }

    .profile-sections {
        display: grid;
        gap: 25px;
    }

    .profile-section {
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .profile-section h2 {
        color: #2c3e50;
        font-size: 1.4rem;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e0e0e0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .profile-section h2 i {
        color: #27ae60;
    }

    .profile-form .form-group {
        margin-bottom: 20px;
    }

    .profile-form label {
        display: block;
        margin-bottom: 8px;
        color: #34495e;
        font-weight: 500;
    }

    .profile-form input {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 1rem;
        transition: border-color 0.3s;
    }

    .profile-form input:focus {
        outline: none;
        border-color: #27ae60;
    }

    .profile-form small {
        display: block;
        margin-top: 5px;
        color: #7f8c8d;
        font-size: 0.85rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .stat-card {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid #27ae60;
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
    }

    .stat-icon.crops {
        background: #d4edda;
        color: #27ae60;
    }

    .stat-icon.sales {
        background: #d1ecf1;
        color: #17a2b8;
    }

    .stat-icon.expenses {
        background: #f8d7da;
        color: #dc3545;
    }

    .stat-info h3 {
        margin: 0 0 5px 0;
        font-size: 1.8rem;
        color: #2c3e50;
    }

    .stat-info p {
        margin: 0;
        color: #7f8c8d;
        font-size: 0.9rem;
    }

    .stat-info small {
        color: #27ae60;
        font-weight: 600;
    }

    .alert {
        padding: 15px 20px;
        border-radius: 6px;
        margin-bottom: 25px;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border-left: 4px solid #dc3545;
    }

    @media (max-width: 768px) {
        .profile-header {
            padding: 30px 20px;
        }

        .profile-avatar i {
            font-size: 4rem;
            padding: 15px;
        }

        .profile-header h1 {
            font-size: 1.5rem;
        }

        .profile-section {
            padding: 20px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php require_once '../includes/footer.php'; ?>