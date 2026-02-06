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
    <title>All Crops - CropManage Admin</title>
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

        .table-section {
            margin-bottom: 2.5rem;
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

        .status-badge {
            display: inline-block;
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-badge.planning {
            background: #dbe4ff;
            color: #364fc7;
        }

        .status-badge.planting {
            background: #d0ebff;
            color: #1864ab;
        }

        .status-badge.growing {
            background: #d1f4e0;
            color: #0d6832;
        }

        .status-badge.ready.to.harvest,
        .status-badge[class*="ready"] {
            background: #ffd8a8;
            color: #d9480f;
        }

        .status-badge.harvested {
            background: #e9ecef;
            color: #495057;
        }

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
    <nav class="admin-navbar">
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

        <div class="admin-navbar-row">
            <ul class="admin-nav-menu">
                <li><a href="dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
                <li><a href="users.php"><i class="fa-solid fa-users"></i> Users</a></li>
                <li><a href="all_crops.php" class="active"><i class="fa-solid fa-leaf"></i> Crops</a></li>
                <li><a href="all_expenses.php"><i class="fa-solid fa-wallet"></i> Expenses</a></li>
                <li><a href="all_sales.php"><i class="fa-solid fa-cart-shopping"></i> Sales</a></li>
                <li><a href="../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

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
</body>

</html>