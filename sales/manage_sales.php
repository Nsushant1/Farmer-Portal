<?php
$page_title = 'Manage Sales - CropManage';
$css_path = '../assets/style.css';
$base_path = '../';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main class="manage-section">
    <div class="section-header">
        <h1>Manage Sales</h1>
        <a href="add_sales.php" class="btn btn-success">+ Add New Sale</a>
    </div>

    <?php
    if (isset($_GET['success'])) {
        echo '<div class="alert alert-success">Sale added successfully!</div>';
    }
    if (isset($_GET['updated'])) {
        echo '<div class="alert alert-success">Sale updated successfully!</div>';
    }
    if (isset($_GET['deleted'])) {
        echo '<div class="alert alert-success">Sale deleted successfully!</div>';
    }

    $query = "SELECT s.*, c.crop_name FROM sales s
              JOIN crops c ON s.crop_id = c.id
              WHERE s.user_id = ?
              ORDER BY s.sale_date DESC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0):
    ?>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Crop Name</th>
                        <th>Sale Date</th>
                        <th>Quantity</th>
                        <th>Price Per Unit</th>
                        <th>Total Amount</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_sales = 0;
                    while ($sale = mysqli_fetch_assoc($result)):
                        $total_sales += $sale['total_amount'];
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($sale['crop_name']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($sale['sale_date'])); ?></td>
                            <td><?php echo number_format($sale['quantity_sold'], 2) . ' ' . htmlspecialchars($sale['quantity_unit']); ?></td>
                            <td class="amount">Rs. <?php echo number_format($sale['price_per_unit'], 2); ?></td>
                            <td class="amount"><strong>Rs. <?php echo number_format($sale['total_amount'], 2); ?></strong></td>
                            <td><?php echo $sale['notes'] ? htmlspecialchars(substr($sale['notes'], 0, 50)) . (strlen($sale['notes']) > 50 ? '...' : '') : '-'; ?></td>
                            <td class="action-column">
                                <a href="edit_sales.php?id=<?php echo $sale['id']; ?>" class="action-btn edit">Edit</a>
                                <a href="delete_sales.php?id=<?php echo $sale['id']; ?>" class="action-btn delete" onclick="return confirm('Are you sure you want to delete this sale?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr style="font-weight: bold; background: #f5f5f5;">
                        <td colspan="4" style="text-align: right;">Total Sales:</td>
                        <td class="amount" style="color: green;">Rs. <?php echo number_format($total_sales, 2); ?></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php
    else:
        echo '<p class="empty-message">No sales records found. <a href="add_sales.php">Add your first sale</a></p>';
    endif;
    mysqli_stmt_close($stmt);
    ?>
</main>

<?php require_once '../includes/footer.php'; ?>
