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
$admin_check = "SELECT is_admin FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $admin_check);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user || $user['is_admin'] != 1) {
    header('Location: ../dashboard/index.php');
    exit;
}

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
    <style>
        :root {
            --primary: #2d5016;
            --primary-light: #4a7c27;
            --secondary: #8bc34a;
            --danger: #f44336;
        }

        .admin-nav {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            padding: 1rem 2rem;
            margin-bottom: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(45, 80, 22, 0.2);
        }

        .admin-nav h2 {
            color: white;
            margin: 0 0 1rem 0;
        }

        .admin-nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .admin-nav ul li a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 4px;
            transition: all 0.3s;
        }

        .admin-nav ul li a:hover,
        .admin-nav ul li a.active {
            background: rgba(255, 255, 255, 0.3);
        }

        h1 {
            color: var(--primary);
            margin-bottom: 1.5rem;
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

        .btn-delete {
            background: var(--danger);
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background 0.3s;
        }

        .btn-delete:hover {
            background: #d32f2f;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 8px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .empty-message {
            text-align: center;
            padding: 2rem;
            color: #666;
            background: white;
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <nav class="admin-nav">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="users.php" class="active">Manage Users</a></li>
            <li><a href="all_crops.php">All Crops</a></li>
            <li><a href="all_expenses.php">All Expenses</a></li>
            <li><a href="all_sales.php">All Sales</a></li>
            <li><a href="../auth/logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <main>
            <h1>Manage Users</h1>

            <?php if (isset($_GET['success']) && $_GET['success'] == 'deleted'): ?>
                <div class="alert alert-success">User deleted successfully!</div>
            <?php endif; ?>

            <?php
            $users_query = "SELECT id, name, email, phone, address, created_at FROM users WHERE is_admin = 0 ORDER BY created_at DESC";
            $users_result = mysqli_query($conn, $users_query);

            if (mysqli_num_rows($users_result) > 0):
            ?>
                <table class="data-table">
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
                                <td><?php echo htmlspecialchars($user_row['name']); ?></td>
                                <td><?php echo htmlspecialchars($user_row['email']); ?></td>
                                <td><?php echo htmlspecialchars($user_row['phone'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($user_row['address'] ?? 'N/A'); ?></td>
                                <td><?php echo date('M d, Y', strtotime($user_row['created_at'])); ?></td>
                                <td>
                                    <a href="users.php?delete=<?php echo $user_row['id']; ?>"
                                        class="btn-delete"
                                        onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty-message">No users found.</p>
            <?php endif; ?>
        </main>
    </div>

    <footer style="margin-top: 3rem; padding: 2rem; text-align: center; background: #f5f5f5; border-top: 1px solid #ddd;">
        <p>&copy; <?php echo date('Y'); ?> CropManage. All rights reserved.</p>
    </footer>

</body>

</html>