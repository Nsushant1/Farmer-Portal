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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Sales - CropManage Admin</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        :root {
            --primary: #2d5016;
            --primary-light: #4a7c27;
            --secondary: #8bc34a;
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

        .data-table tfoot tr {
            background: rgba(139, 195, 74, 0.1);
            font-weight: bold;
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
            <li><a href="users.php">Manage Users</a></li>
            <li><a href="all_crops.php">All Crops</a></li>
            <li><a href="all_expenses.php">All Expenses</a></li>
            <li><a href="all_sales.php" class="active">All Sales</a></li>
            <li><a href="../auth/logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <main>
            <h1>All Sales Overview</h1>

            <?php
            $sales_query = "SELECT s.*, u.name as farmer_name, c.crop_name 
                           FROM sales s 
                           JOIN users u ON s.user_id = u.id 
                           JOIN crops c ON s.crop_id = c.id 
                           ORDER BY s.sale_date DESC";
            $sales_result = mysqli_query($conn, $sales_query);

            if (mysqli_num_rows($sales_result) > 0):
                $total_sales = 0;
            ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Farmer</th>
                            <th>Crop</th>
                            <th>Quantity</th>
                            <th>Price/Unit</th>
                            <th>Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($sale = mysqli_fetch_assoc($sales_result)):
                            $total_sales += $sale['total_amount'];
                        ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($sale['sale_date'])); ?></td>
                                <td><?php echo htmlspecialchars($sale['farmer_name']); ?></td>
                                <td><?php echo htmlspecialchars($sale['crop_name']); ?></td>
                                <td><?php echo $sale['quantity_sold'] . ' ' . $sale['quantity_unit']; ?></td>
                                <td>Rs. <?php echo number_format($sale['price_per_unit'], 2); ?></td>
                                <td>Rs. <?php echo number_format($sale['total_amount'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" style="text-align: right; color: var(--primary);">Total Sales:</td>
                            <td style="color: var(--primary);">Rs. <?php echo number_format($total_sales, 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            <?php else: ?>
                <p class="empty-message">No sales records found.</p>
            <?php endif; ?>
        </main>
    </div>

    <footer style="margin-top: 3rem; padding: 2rem; text-align: center; background: #f5f5f5; border-top: 1px solid #ddd;">
        <p>&copy; <?php echo date('Y'); ?> CropManage. All rights reserved.</p>
    </footer>

</body>

</html>