<?php
require_once '../config/db_connection.php';
require_once '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$sales_id = $_GET['id'] ?? '';
$success = '';
$error = '';

// Fetch sales record
$query = "SELECT * FROM sales WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $sales_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$sales = mysqli_fetch_assoc($result);

if (!$sales) {
    header('Location: ' . BASE_URL . 'sales/manage_sales.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $crop_id = $_POST['crop_id'] ?? '';
    $sale_date = $_POST['sale_date'] ?? '';
    $quantity_sold = $_POST['quantity_sold'] ?? '';
    $quantity_unit = $_POST['quantity_unit'] ?? 'kg';
    $price_per_unit = $_POST['price_per_unit'] ?? '';
    $buyer_name = $_POST['buyer_name'] ?? '';
    $buyer_phone = $_POST['buyer_phone'] ?? '';
    $buyer_location = $_POST['buyer_location'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';
    $payment_status = $_POST['payment_status'] ?? 'Pending';
    $notes = $_POST['notes'] ?? '';

    if (empty($crop_id) || empty($sale_date) || empty($quantity_sold) || empty($price_per_unit)) {
        $error = 'Please fill in all required fields';
    } else {
        $total_amount = $quantity_sold * $price_per_unit;

        $query = "UPDATE sales SET crop_id=?, sale_date=?, quantity_sold=?, quantity_unit=?, price_per_unit=?, total_amount=?, buyer_name=?, buyer_phone=?, buyer_location=?, payment_method=?, payment_status=?, notes=?
                  WHERE id=? AND user_id=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'iisddsssssssii', $crop_id, $sale_date, $quantity_sold, $quantity_unit, $price_per_unit, $total_amount, $buyer_name, $buyer_phone, $buyer_location, $payment_method, $payment_status, $notes, $sales_id, $user_id);

        if (mysqli_stmt_execute($stmt)) {
            $success = 'Sales record updated successfully!';
            header('refresh:2; url=' . BASE_URL . 'sales/manage_sales.php');
        } else {
            $error = 'Failed to update sales record: ' . mysqli_error($conn);
        }
    }
}

// Fetch crops
$query = "SELECT id, crop_name FROM crops WHERE user_id = ? ORDER BY crop_name";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$crops_result = mysqli_stmt_get_result($stmt);
?>

<?php require_once '../includes/navbar.php'; ?>

<main class="manage-section">
    <div class="section-header">
        <h1>Edit Sales Record</h1>
        <a href="<?php echo BASE_URL; ?>sales/manage_sales.php" class="btn btn-secondary">Back to Sales</a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" class="form-container">
        <div class="form-group">
            <label for="crop_id">Select Crop *</label>
            <select id="crop_id" name="crop_id" required>
                <?php
                mysqli_data_seek($crops_result, 0);
                while ($crop = mysqli_fetch_assoc($crops_result)):
                ?>
                    <option value="<?php echo $crop['id']; ?>" <?php echo $crop['id'] == $sales['crop_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($crop['crop_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="sale_date">Sale Date *</label>
                <input type="date" id="sale_date" name="sale_date" required value="<?php echo $sales['sale_date']; ?>">
            </div>
            <div class="form-group">
                <label for="quantity_sold">Quantity Sold *</label>
                <input type="number" id="quantity_sold" name="quantity_sold" step="0.01" required value="<?php echo $sales['quantity_sold']; ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="quantity_unit">Unit</label>
                <select id="quantity_unit" name="quantity_unit">
                    <option value="kg" <?php echo $sales['quantity_unit'] == 'kg' ? 'selected' : ''; ?>>Kg</option>
                    <option value="quintal" <?php echo $sales['quantity_unit'] == 'quintal' ? 'selected' : ''; ?>>Quintal</option>
                    <option value="ton" <?php echo $sales['quantity_unit'] == 'ton' ? 'selected' : ''; ?>>Ton</option>
                    <option value="liter" <?php echo $sales['quantity_unit'] == 'liter' ? 'selected' : ''; ?>>Liter</option>
                </select>
            </div>
            <div class="form-group">
                <label for="price_per_unit">Price Per Unit (₹) *</label>
                <input type="number" id="price_per_unit" name="price_per_unit" step="0.01" required value="<?php echo $sales['price_per_unit']; ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Total Amount: ₹<span id="total_amount"><?php echo number_format($sales['total_amount'], 2); ?></span></label>
        </div>

        <div class="form-group">
            <label for="buyer_name">Buyer Name</label>
            <input type="text" id="buyer_name" name="buyer_name" value="<?php echo htmlspecialchars($sales['buyer_name'] ?? ''); ?>">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="buyer_phone">Buyer Phone</label>
                <input type="tel" id="buyer_phone" name="buyer_phone" value="<?php echo htmlspecialchars($sales['buyer_phone'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="buyer_location">Buyer Location</label>
                <input type="text" id="buyer_location" name="buyer_location" value="<?php echo htmlspecialchars($sales['buyer_location'] ?? ''); ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="payment_method">Payment Method</label>
                <select id="payment_method" name="payment_method">
                    <option value="Cash" <?php echo $sales['payment_method'] == 'Cash' ? 'selected' : ''; ?>>Cash</option>
                    <option value="Bank Transfer" <?php echo $sales['payment_method'] == 'Bank Transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                    <option value="Check" <?php echo $sales['payment_method'] == 'Check' ? 'selected' : ''; ?>>Check</option>
                    <option value="Online" <?php echo $sales['payment_method'] == 'Online' ? 'selected' : ''; ?>>Online</option>
                </select>
            </div>
            <div class="form-group">
                <label for="payment_status">Payment Status</label>
                <select id="payment_status" name="payment_status">
                    <option value="Pending" <?php echo $sales['payment_status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Received" <?php echo $sales['payment_status'] == 'Received' ? 'selected' : ''; ?>>Received</option>
                    <option value="Partial" <?php echo $sales['payment_status'] == 'Partial' ? 'selected' : ''; ?>>Partial</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="3"><?php echo htmlspecialchars($sales['notes'] ?? ''); ?></textarea>
        </div>

        <button type="submit" class="btn btn-success">Update Sales Record</button>
    </form>
</main>

<script>
    document.getElementById('quantity_sold').addEventListener('change', calculateTotal);
    document.getElementById('price_per_unit').addEventListener('change', calculateTotal);

    function calculateTotal() {
        const quantity = parseFloat(document.getElementById('quantity_sold').value) || 0;
        const price = parseFloat(document.getElementById('price_per_unit').value) || 0;
        const total = (quantity * price).toFixed(2);
        document.getElementById('total_amount').textContent = total;
    }
</script>

<?php require_once '../includes/footer.php'; ?>
