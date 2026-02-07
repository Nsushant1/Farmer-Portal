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

// Handle user blocking
if (isset($_GET['block']) && is_numeric($_GET['block'])) {
    $block_id = intval($_GET['block']);
    $block_query = "UPDATE users SET status = 'blocked' WHERE id = ? AND role = 'user'";
    $block_stmt = mysqli_prepare($conn, $block_query);
    mysqli_stmt_bind_param($block_stmt, 'i', $block_id);
    mysqli_stmt_execute($block_stmt);
    mysqli_stmt_close($block_stmt);
    header('Location: users.php?success=blocked');
    exit;
}

// Handle user unblocking
if (isset($_GET['unblock']) && is_numeric($_GET['unblock'])) {
    $unblock_id = intval($_GET['unblock']);
    $unblock_query = "UPDATE users SET status = 'active' WHERE id = ? AND role = 'user'";
    $unblock_stmt = mysqli_prepare($conn, $unblock_query);
    mysqli_stmt_bind_param($unblock_stmt, 'i', $unblock_id);
    mysqli_stmt_execute($unblock_stmt);
    mysqli_stmt_close($unblock_stmt);
    header('Location: users.php?success=unblocked');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - CropManage Admin</title>
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
                <li><a href="users.php" class="active"><i class="fa-solid fa-users"></i> Users</a></li>
                <li><a href="all_crops.php"><i class="fa-solid fa-leaf"></i> Crops</a></li>
                <li><a href="all_expenses.php"><i class="fa-solid fa-wallet"></i> Expenses</a></li>
                <li><a href="all_sales.php"><i class="fa-solid fa-cart-shopping"></i> Sales</a></li>
                <li><a href="#" class="logout-link"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="content-wrapper">
        <div class="page-header">
            <h2>Manage Users</h2>
            <p>View and manage all registered farmers on the platform</p>
        </div>

        <?php if (isset($_GET['success']) && $_GET['success'] == 'blocked'): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <span>User blocked successfully!</span>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success']) && $_GET['success'] == 'unblocked'): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <span>User unblocked successfully!</span>
            </div>
        <?php endif; ?>

        <div class="table-section">
            <div class="table-wrapper">
                <?php
                $users_query = "SELECT id, name, email, phone, address, status, created_at FROM users WHERE role = 'user' ORDER BY created_at DESC";
                $users_result = mysqli_query($conn, $users_query);

                if (mysqli_num_rows($users_result) > 0):
                ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user_row = mysqli_fetch_assoc($users_result)): ?>
                                <tr>
                                    <td><?php echo $user_row['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($user_row['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($user_row['email']); ?></td>
                                    <td><?php echo !empty($user_row['phone']) ? htmlspecialchars($user_row['phone']) : 'N/A'; ?></td>
                                    <td><?php echo !empty($user_row['address']) ? htmlspecialchars($user_row['address']) : 'N/A'; ?></td>
                                    <td>
                                        <?php
                                        $status = $user_row['status'] ?? 'active';
                                        if ($status == 'blocked'):
                                        ?>
                                            <span class="status-badge status-blocked">Blocked</span>
                                        <?php else: ?>
                                            <span class="status-badge status-active">Active</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user_row['created_at'])); ?></td>
                                    <td>
                                        <?php
                                        $status = $user_row['status'] ?? 'active';
                                        if ($status == 'blocked'):
                                        ?>
                                            <a href="users.php?unblock=<?php echo $user_row['id']; ?>"
                                                class="btn-unblock"
                                                onclick="return confirm('Are you sure you want to unblock this user?')">
                                                <i class="fa-solid fa-unlock"></i> Unblock
                                            </a>
                                        <?php else: ?>
                                            <a href="users.php?block=<?php echo $user_row['id']; ?>"
                                                class="btn-block"
                                                onclick="return confirm('Are you sure you want to block this user? They will not be able to login.')">
                                                <i class="fa-solid fa-ban"></i> Block
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-users-slash"></i>
                        <h4>No Users Found</h4>
                        <p>Registered farmers will appear here</p>
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