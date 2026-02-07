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
    <title>All Crops - CropManage Admin</title>
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
                <li><a href="dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
                <li><a href="users.php"><i class="fa-solid fa-users"></i> Users</a></li>
                <li><a href="all_crops.php" class="active"><i class="fa-solid fa-leaf"></i> Crops</a></li>
                <li><a href="all_expenses.php"><i class="fa-solid fa-wallet"></i> Expenses</a></li>
                <li><a href="all_sales.php"><i class="fa-solid fa-cart-shopping"></i> Sales</a></li>
                <li><a href="#" class="logout-link"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="content-wrapper">
        <div class="page-header">
            <h2>All Crops Overview</h2>
            <p>Complete list of all crops across all farmers</p>
        </div>

        <div class="table-section">
            <div class="table-wrapper">
                <?php
                $crops_query = "SELECT c.*, u.name as farmer_name 
                               FROM crops c 
                               JOIN users u ON c.user_id = u.id 
                               ORDER BY c.created_at DESC";
                $crops_result = mysqli_query($conn, $crops_query);

                if (mysqli_num_rows($crops_result) > 0):
                ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Crop Name</th>
                                <th>Type</th>
                                <th>Farmer</th>
                                <th>Planting Date</th>
                                <th>Expected Harvest</th>
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
                                    <td><?php echo $crop['expected_harvest_date'] ? date('M d, Y', strtotime($crop['expected_harvest_date'])) : 'N/A'; ?></td>
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
                        <h4>No Crops Found</h4>
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