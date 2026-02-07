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

// Handle user deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $delete_query = "DELETE FROM users WHERE id = ? AND is_admin = 0";
    $delete_stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($delete_stmt, 'i', $delete_id);
    mysqli_stmt_execute($delete_stmt);
    header('Location: users.php?success=deleted');
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

        <?php if (isset($_GET['success']) && $_GET['success'] == 'deleted'): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <span>User deleted successfully!</span>
            </div>
        <?php endif; ?>

        <div class="table-section">
            <div class="table-wrapper">
                <?php
                $users_query = "SELECT id, name, email, phone, address, created_at FROM users WHERE is_admin = 0 ORDER BY created_at DESC";
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
                                    <td><?php echo htmlspecialchars($user_row['phone'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($user_row['address'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($user_row['created_at'])); ?></td>
                                    <td>
                                        <a href="users.php?delete=<?php echo $user_row['id']; ?>"
                                            class="btn-delete"
                                            onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                            <i class="fa-solid fa-trash"></i> Delete
                                        </a>
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