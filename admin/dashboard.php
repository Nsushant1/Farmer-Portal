<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../config/db_connection.php';

$user_id = $_SESSION['user_id'];

// Check if user is admin
$admin_check = "SELECT role, name FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $admin_check);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user || $user['role'] !== 'admin') {
    header('Location: ../dashboard/index.php');
    exit;
}

$user_name = $user['name'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CropManage</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Admin Navigation -->
    <nav class="admin-navbar">
        <!-- TOP ROW -->
        <div class="admin-navbar-row">
            <div class="admin-brand">
                <div class="admin-brand-icon">
                    <i class="fa-solid fa-leaf"></i>
                </div>
                <div class="admin-brand-text">
                    <h1>CropManage</h1>
                    <span>Admin Panel</span>
                </div>
            </div>

            <div class="admin-user">
                <div class="admin-avatar">
                    <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                </div>
                <div class="admin-user-info">
                    <strong><?php echo htmlspecialchars($user_name); ?></strong>
                    <span>Administrator</span>
                </div>
            </div>
        </div>

        <!-- MENU ROW -->
        <div class="admin-navbar-row">
            <ul class="admin-nav-menu">
                <li><a href="dashboard.php" class="active"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
                <li><a href="users.php"><i class="fa-solid fa-users"></i> Users</a></li>
                <li><a href="all_crops.php"><i class="fa-solid fa-leaf"></i> Crops</a></li>
                <li><a href="all_expenses.php"><i class="fa-solid fa-wallet"></i> Expenses</a></li>
                <li><a href="all_sales.php"><i class="fa-solid fa-cart-shopping"></i> Sales</a></li>
                <li><a href="#" class="logout-link"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="content-wrapper">
        <!-- Page Header -->
        <div class="page-header">
            <h2>Dashboard Overview</h2>
            <p>Monitor your platform's performance and user activity</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <?php
            $users_query = "SELECT COUNT(*) as total_users FROM users WHERE role = 'user'";
            $users_result = mysqli_query($conn, $users_query);
            $users_data = mysqli_fetch_assoc($users_result);

            $crops_query = "SELECT COUNT(*) as total_crops FROM crops";
            $crops_result = mysqli_query($conn, $crops_query);
            $crops_data = mysqli_fetch_assoc($crops_result);

            $expenses_query = "SELECT SUM(amount) as total_expenses FROM expenses";
            $expenses_result = mysqli_query($conn, $expenses_query);
            $expenses_data = mysqli_fetch_assoc($expenses_result);

            $sales_query = "SELECT SUM(total_amount) as total_sales FROM sales";
            $sales_result = mysqli_query($conn, $sales_query);
            $sales_data = mysqli_fetch_assoc($sales_result);
            ?>

            <div class="stat-card">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <h3>Total Farmers</h3>
                        <div class="stat-value"><?php echo $users_data['total_users'] ?? 0; ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <h3>Total Crops</h3>
                        <div class="stat-value"><?php echo $crops_data['total_crops'] ?? 0; ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fa-solid fa-seedling"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <h3>Total Expenses</h3>
                        <div class="stat-value">₹<?php echo number_format($expenses_data['total_expenses'] ?? 0, 0); ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <h3>Total Sales</h3>
                        <div class="stat-value">₹<?php echo number_format($sales_data['total_sales'] ?? 0, 0); ?></div>
                    </div>
                    <div class="stat-icon">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Users Table -->
        <?php
        $recent_users = "SELECT name, email, created_at FROM users WHERE role = 'user' ORDER BY created_at DESC LIMIT 10";
        $recent_result = mysqli_query($conn, $recent_users);
        $user_count = mysqli_num_rows($recent_result);
        ?>

        <div class="table-section">
            <h3><i class="fa-solid fa-user-plus"></i> Recent Farmer Registrations</h3>
            <div class="table-wrapper">
                <?php if ($user_count > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Farmer Name</th>
                                <th>Email Address</th>
                                <th>Registration Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user_row = mysqli_fetch_assoc($recent_result)): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($user_row['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($user_row['email']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($user_row['created_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-users-slash"></i>
                        <h4>No Farmers Yet</h4>
                        <p>New farmer registrations will appear here</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Crops Table -->
        <?php
        $recent_crops = "SELECT c.crop_name, c.crop_type, c.planting_date, c.status, u.name as farmer_name 
                        FROM crops c 
                        JOIN users u ON c.user_id = u.id 
                        ORDER BY c.created_at DESC LIMIT 10";
        $crops_result = mysqli_query($conn, $recent_crops);
        $crop_count = mysqli_num_rows($crops_result);
        ?>

        <div class="table-section">
            <h3><i class="fa-solid fa-leaf"></i> Recently Added Crops</h3>
            <div class="table-wrapper">
                <?php if ($crop_count > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Crop Name</th>
                                <th>Type</th>
                                <th>Farmer</th>
                                <th>Planting Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($crop = mysqli_fetch_assoc($crops_result)): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($crop['crop_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($crop['crop_type']); ?></td>
                                    <td><?php echo htmlspecialchars($crop['farmer_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($crop['planting_date'])); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $crop['status'])); ?>">
                                            <?php echo htmlspecialchars($crop['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-seedling"></i>
                        <h4>No Crops Added</h4>
                        <p>Crop records will appear here once farmers start adding them</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>

    <!-- Logout Confirmation Script -->
    <script src="../assets/admin-logout.js"></script>
</body>

</html>