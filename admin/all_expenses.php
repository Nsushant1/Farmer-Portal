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
    <title>All Expenses - CropManage Admin</title>
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
                <li><a href="all_crops.php"><i class="fa-solid fa-leaf"></i> Crops</a></li>
                <li><a href="all_expenses.php" class="active"><i class="fa-solid fa-wallet"></i> Expenses</a></li>
                <li><a href="all_sales.php"><i class="fa-solid fa-cart-shopping"></i> Sales</a></li>
                <li><a href="#" class="logout-link"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="content-wrapper">
        <div class="page-header">
            <h2>All Expenses Overview</h2>
            <p>Complete list of all expenses tracked across all farmers</p>
        </div>

        <div class="table-section">
            <div class="table-wrapper">
                <?php
                $expenses_query = "SELECT e.*, u.name as farmer_name, c.crop_name 
                                  FROM expenses e 
                                  JOIN users u ON e.user_id = u.id 
                                  JOIN crops c ON e.crop_id = c.id 
                                  ORDER BY e.expense_date DESC";
                $expenses_result = mysqli_query($conn, $expenses_query);

                if (mysqli_num_rows($expenses_result) > 0):
                    $total_expenses = 0;
                ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Farmer</th>
                                <th>Crop</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($expense = mysqli_fetch_assoc($expenses_result)):
                                $total_expenses += $expense['amount'];
                            ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($expense['expense_date'])); ?></td>
                                    <td><strong><?php echo htmlspecialchars($expense['farmer_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($expense['crop_name']); ?></td>
                                    <td><?php echo htmlspecialchars($expense['expense_category']); ?></td>
                                    <td><?php echo htmlspecialchars($expense['description'] ?? 'N/A'); ?></td>
                                    <td><strong>₹<?php echo number_format($expense['amount'], 2); ?></strong></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" style="text-align: right;">Total Expenses:</td>
                                <td>₹<?php echo number_format($total_expenses, 2); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-wallet"></i>
                        <h4>No Expenses Found</h4>
                        <p>Expense records will appear here once farmers start tracking them</p>
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