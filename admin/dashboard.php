<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../config/db_connection.php';

$user_id = $_SESSION['user_id'];

// Check if user is admin
$admin_check = "SELECT is_admin, name FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $admin_check);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user || $user['is_admin'] != 1) {
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f8f9fa;
            font-family: 'Inter', 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Admin Navigation */
        .admin-navbar {
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .admin-navbar-row {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1rem 2.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-navbar-row+.admin-navbar-row {
            border-top: 1px solid #e9ecef;
            padding: 0;
        }

        /* Brand */
        .admin-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .admin-brand-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #2d5016 0%, #4a7c27 100%);
            color: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            box-shadow: 0 2px 6px rgba(45, 80, 22, 0.2);
        }

        .admin-brand-text h1 {
            margin: 0;
            font-size: 1.4rem;
            color: #1a1a1a;
            font-weight: 700;
        }

        .admin-brand-text span {
            font-size: 0.75rem;
            color: #6c757d;
            font-weight: 500;
        }

        /* User Info */
        .admin-user {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: #f8f9fa;
            padding: 0.5rem 1.2rem;
            border-radius: 50px;
            border: 1px solid #e9ecef;
        }

        .admin-avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #2d5016 0%, #4a7c27 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .admin-user-info strong {
            display: block;
            font-size: 0.9rem;
            color: #1a1a1a;
            font-weight: 600;
        }

        .admin-user-info span {
            display: block;
            font-size: 0.75rem;
            color: #6c757d;
        }

        /* Navigation Menu */
        .admin-nav-menu {
            list-style: none;
            display: flex;
            width: 100%;
            margin: 0;
            padding: 0;
        }

        .admin-nav-menu li {
            flex: 1;
        }

        .admin-nav-menu a {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            padding: 1rem;
            text-decoration: none;
            color: #495057;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
        }

        .admin-nav-menu a:hover {
            color: #2d5016;
            background: #f8f9fa;
            border-bottom-color: #2d5016;
        }

        .admin-nav-menu a.active {
            color: #2d5016;
            background: #f1f8e9;
            border-bottom-color: #2d5016;
            font-weight: 600;
        }

        .admin-nav-menu a i {
            font-size: 1.1rem;
        }

        /* Main Content */
        .content-wrapper {
            flex: 1;
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
            padding: 2.5rem 2.5rem;
        }

        /* Page Header */
        .page-header {
            margin-bottom: 2.5rem;
        }

        .page-header h2 {
            color: #1a1a1a;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            color: #6c757d;
            font-size: 1rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: white;
            padding: 1.8rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .stat-card-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-info h3 {
            font-size: 0.8rem;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.75rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1a1a1a;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-card:nth-child(1) .stat-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .stat-card:nth-child(2) .stat-icon {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }

        .stat-card:nth-child(3) .stat-icon {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .stat-card:nth-child(4) .stat-icon {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }

        /* Table Section */
        .table-section {
            margin-bottom: 2.5rem;
        }

        .table-section h3 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .table-section h3 i {
            color: #2d5016;
            font-size: 1.2rem;
        }

        .table-wrapper {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            border: 1px solid #e9ecef;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 1.1rem 1.5rem;
            text-align: left;
            font-size: 0.82rem;
            font-weight: 700;
            color: #495057;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
        }

        td {
            padding: 1.2rem 1.5rem;
            font-size: 0.95rem;
            color: #495057;
            border-bottom: 1px solid #f1f3f5;
        }

        tbody tr {
            transition: background 0.2s ease;
        }

        tbody tr:hover {
            background: #f8f9fa;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        td strong {
            color: #1a1a1a;
            font-weight: 600;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-badge.growing,
        .status-badge.planting {
            background: #d1f4e0;
            color: #0d6832;
        }

        .status-badge.harvested,
        .status-badge.completed {
            background: #ffd8a8;
            color: #d9480f;
        }

        .status-badge.planning {
            background: #dbe4ff;
            color: #364fc7;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-state i {
            font-size: 3.5rem;
            color: #dee2e6;
            margin-bottom: 1.2rem;
        }

        .empty-state h4 {
            color: #495057;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .empty-state p {
            color: #6c757d;
            font-size: 0.95rem;
        }

        /* Responsive */
        @media (max-width: 1024px) {

            .admin-navbar-row,
            .content-wrapper {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .admin-navbar-row {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .admin-nav-menu {
                flex-direction: column;
                width: 100%;
            }

            .admin-nav-menu a {
                justify-content: flex-start;
                padding: 1rem 1.5rem;
                border-bottom: 1px solid #e9ecef;
                border-bottom-width: 1px;
            }

            .content-wrapper {
                padding: 1.5rem 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .page-header h2 {
                font-size: 1.6rem;
            }

            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            th,
            td {
                padding: 0.9rem 1rem;
                font-size: 0.85rem;
            }
        }
    </style>
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
                <li><a href="../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
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
            $users_query = "SELECT COUNT(*) as total_users FROM users WHERE is_admin = 0";
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
        $recent_users = "SELECT name, email, created_at FROM users WHERE is_admin = 0 ORDER BY created_at DESC LIMIT 10";
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
</body>

</html>