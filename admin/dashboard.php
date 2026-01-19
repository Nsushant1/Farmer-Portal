<?php
session_start();

// Check if user is logged in and is admin
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
    <style>
        :root {
            --primary: #2d5016;
            --primary-light: #4a7c27;
            --secondary: #8bc34a;
        }

        /* Enhanced Admin Navigation */
        .admin-nav {
            background: linear-gradient(135deg, #2d5016 0%, #4a7c27 100%);
            padding: 0;
            margin-bottom: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(45, 80, 22, 0.3);
            overflow: hidden;
        }

        .admin-nav-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .admin-nav-header h2 {
            color: white;
            margin: 0;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .admin-nav-header h2::before {
            content: "🌱";
            font-size: 1.8rem;
        }

        .admin-user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: white;
        }

        .admin-user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .admin-user-name {
            font-size: 0.95rem;
            font-weight: 500;
        }

        .admin-nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 0;
        }

        .admin-nav ul li {
            flex: 1;
        }

        .admin-nav ul li a {
            color: white;
            text-decoration: none;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: transparent;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
            position: relative;
            font-weight: 500;
        }

        .admin-nav ul li:last-child a {
            border-right: none;
        }

        .admin-nav ul li a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .admin-nav ul li a.active {
            background: rgba(255, 255, 255, 0.2);
        }

        .admin-nav ul li a.active::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: #8bc34a;
        }

        .admin-nav ul li a .icon {
            font-size: 1.2rem;
        }

        /* Dashboard Styles */
        .dashboard-header {
            margin-bottom: 2rem;
        }

        .dashboard-header h1 {
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.3s, box-shadow 0.3s;
            border-left: 4px solid var(--primary);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(45, 80, 22, 0.2);
        }

        .stat-icon {
            font-size: 2.5rem;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(139, 195, 74, 0.1);
            border-radius: 12px;
        }

        .stat-content h3 {
            margin: 0;
            font-size: 0.9rem;
            color: #666;
            font-weight: 500;
        }

        .stat-value {
            margin: 0.25rem 0 0 0;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .data-table th,
        .data-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .data-table th {
            background: var(--primary);
            color: white;
            font-weight: 600;
        }

        .data-table tr:hover {
            background: rgba(139, 195, 74, 0.05);
        }

        .section-title {
            margin: 2rem 0 1rem 0;
            color: var(--primary);
        }

        .empty-message {
            text-align: center;
            padding: 2rem;
            color: #666;
            background: white;
            border-radius: 8px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .admin-nav-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .admin-nav ul {
                flex-direction: column;
            }

            .admin-nav ul li a {
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                justify-content: flex-start;
                padding-left: 2rem;
            }
        }
    </style>
</head>

<body>
    <nav class="admin-nav">
        <div class="admin-nav-header">
            <h2>CropManage Admin</h2>
            <div class="admin-user-info">
                <div class="admin-user-avatar"><?php echo strtoupper(substr($user_name, 0, 1)); ?></div>
                <span class="admin-user-name"><?php echo htmlspecialchars($user_name); ?></span>
            </div>
        </div>
        <ul>
            <li><a href="dashboard.php" class="active"><span class="icon">📊</span>Dashboard</a></li>
            <li><a href="users.php"><span class="icon">👥</span>Users</a></li>
            <li><a href="all_crops.php"><span class="icon">🌾</span>Crops</a></li>
            <li><a href="all_expenses.php"><span class="icon">💰</span>Expenses</a></li>
            <li><a href="all_sales.php"><span class="icon">💵</span>Sales</a></li>
            <li><a href="../auth/logout.php"><span class="icon">🚪</span>Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <main class="dashboard">
            <section class="dashboard-header">
                <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
                <p>Platform overview and statistics</p>
            </section>

            <section class="dashboard-stats">
                <?php
                // Get total users
                $users_query = "SELECT COUNT(*) as total_users FROM users WHERE is_admin = 0";
                $users_result = mysqli_query($conn, $users_query);
                $users_data = mysqli_fetch_assoc($users_result);

                // Get total crops
                $crops_query = "SELECT COUNT(*) as total_crops FROM crops";
                $crops_result = mysqli_query($conn, $crops_query);
                $crops_data = mysqli_fetch_assoc($crops_result);

                // Get total expenses
                $expenses_query = "SELECT SUM(amount) as total_expenses FROM expenses";
                $expenses_result = mysqli_query($conn, $expenses_query);
                $expenses_data = mysqli_fetch_assoc($expenses_result);

                // Get total sales
                $sales_query = "SELECT SUM(total_amount) as total_sales FROM sales";
                $sales_result = mysqli_query($conn, $sales_query);
                $sales_data = mysqli_fetch_assoc($sales_result);
                ?>

                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-content">
                        <h3>Total Farmers</h3>
                        <p class="stat-value"><?php echo $users_data['total_users'] ?? 0; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">🌾</div>
                    <div class="stat-content">
                        <h3>Total Crops</h3>
                        <p class="stat-value"><?php echo $crops_data['total_crops'] ?? 0; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-content">
                        <h3>Total Expenses</h3>
                        <p class="stat-value">Rs. <?php echo number_format($expenses_data['total_expenses'] ?? 0, 2); ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">💵</div>
                    <div class="stat-content">
                        <h3>Total Sales</h3>
                        <p class="stat-value">Rs. <?php echo number_format($sales_data['total_sales'] ?? 0, 2); ?></p>
                    </div>
                </div>
            </section>

            <section class="recent-users">
                <h2 class="section-title">Recent User Registrations</h2>
                <?php
                $recent_users = "SELECT name, email, created_at FROM users WHERE is_admin = 0 ORDER BY created_at DESC LIMIT 10";
                $recent_result = mysqli_query($conn, $recent_users);

                if (mysqli_num_rows($recent_result) > 0):
                ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Registered On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user_row = mysqli_fetch_assoc($recent_result)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user_row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user_row['email']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($user_row['created_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="empty-message">No users registered yet.</p>
                <?php endif; ?>
            </section>

            <section class="recent-crops">
                <h2 class="section-title">Recent Crops Added</h2>
                <?php
                $recent_crops = "SELECT c.crop_name, c.crop_type, c.planting_date, c.status, u.name as farmer_name 
                                FROM crops c 
                                JOIN users u ON c.user_id = u.id 
                                ORDER BY c.created_at DESC LIMIT 10";
                $crops_result = mysqli_query($conn, $recent_crops);

                if (mysqli_num_rows($crops_result) > 0):
                ?>
                    <table class="data-table">
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
                                    <td><?php echo htmlspecialchars($crop['crop_name']); ?></td>
                                    <td><?php echo htmlspecialchars($crop['crop_type']); ?></td>
                                    <td><?php echo htmlspecialchars($crop['farmer_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($crop['planting_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($crop['status']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="empty-message">No crops added yet.</p>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <footer style="margin-top: 3rem; padding: 2rem; text-align: center; background: #f5f5f5; border-top: 1px solid #ddd;">
        <p>&copy; <?php echo date('Y'); ?> CropManage. All rights reserved.</p>
    </footer>

</body>

</html>