<?php
$page_title = 'Edit Sale - CropManage';
$css_path = '../assets/style.css';
$base_path = '../';
require_once '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$sales_id = $_GET['id'] ?? '';
$error = '';
$success = '';

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
    $crop_id = intval($_POST['crop_id'] ?? 0);
    $sale_date = $_POST['sale_date'] ?? '';
    $quantity_sold = floatval($_POST['quantity_sold'] ?? 0);
    $quantity_unit = mysqli_real_escape_string($conn, $_POST['quantity_unit'] ?? 'kg');
    $price_per_unit = floatval($_POST['price_per_unit'] ?? 0);
    $total_amount = $quantity_sold * $price_per_unit;
    $notes = mysqli_real_escape_string($conn, $_POST['notes'] ?? '');

    if (empty($crop_id) || empty($sale_date) || empty($quantity_sold) || empty($price_per_unit)) {
        $error = 'Please fill in all required fields';
    } else {
        $query = "UPDATE sales SET crop_id=?, sale_date=?, quantity_sold=?, quantity_unit=?, price_per_unit=?, total_amount=?, notes=?
                  WHERE id=? AND user_id=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'isdsddsis', $crop_id, $sale_date, $quantity_sold, $quantity_unit, $price_per_unit, $total_amount, $notes, $sales_id, $user_id);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header('Location: manage_sales.php?updated=1');
            exit;
        } else {
            $error = 'Error updating sale. Please try again.';
        }
        mysqli_stmt_close($stmt);
    }
}

// Get user's crops
$crops_query = "SELECT id, crop_name FROM crops WHERE user_id = ? ORDER BY crop_name";
$crops_stmt = mysqli_prepare($conn, $crops_query);
mysqli_stmt_bind_param($crops_stmt, 'i', $user_id);
mysqli_stmt_execute($crops_stmt);
$crops_result = mysqli_stmt_get_result($crops_stmt);

require_once '../includes/navbar.php';
?>

<main class="form-container">
    <div class="form-card">
        <h1>Edit Sale</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="crop_id">Select Crop *</label>
                    <select id="crop_id" name="crop_id" required>
                        <option value="">Choose a crop</option>
                        <?php while ($crop = mysqli_fetch_assoc($crops_result)): ?>
                            <option value="<?php echo $crop['id']; ?>" <?php echo $crop['id'] == $sales['crop_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($crop['crop_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="sale_date">Sale Date *</label>
                    <input type="date" id="sale_date" name="sale_date" required max="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($sales['sale_date']); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="quantity_sold">Quantity Sold *</label>
                    <input type="number" id="quantity_sold" name="quantity_sold" required step="0.01" min="0" placeholder="e.g., 100" oninput="calculateTotal()" value="<?php echo htmlspecialchars($sales['quantity_sold']); ?>">
                </div>
                <div class="form-group">
                    <label for="quantity_unit">Unit *</label>
                    <select id="quantity_unit" name="quantity_unit" required>
                        <option value="kg" <?php echo $sales['quantity_unit'] == 'kg' ? 'selected' : ''; ?>>Kilograms (kg)</option>
                        <option value="quintal" <?php echo $sales['quantity_unit'] == 'quintal' ? 'selected' : ''; ?>>Quintal</option>
                        <option value="ton" <?php echo $sales['quantity_unit'] == 'ton' ? 'selected' : ''; ?>>Ton</option>
                        <option value="bag" <?php echo $sales['quantity_unit'] == 'bag' ? 'selected' : ''; ?>>Bag</option>
                        <option value="piece" <?php echo $sales['quantity_unit'] == 'piece' ? 'selected' : ''; ?>>Piece</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="price_per_unit">Price Per Unit (Rs.) *</label>
                    <input type="number" id="price_per_unit" name="price_per_unit" required step="0.01" min="0" placeholder="e.g., 50.00" oninput="calculateTotal()" value="<?php echo htmlspecialchars($sales['price_per_unit']); ?>">
                </div>
                <div class="form-group">
                    <label for="total_amount">Total Amount (Rs.)</label>
                    <input type="number" id="total_amount" step="0.01" readonly style="background: #f5f5f5;" placeholder="Auto-calculated" value="<?php echo htmlspecialchars($sales['total_amount']); ?>">
                </div>
            </div>

            <div class="form-group full-width">
                <label for="notes">Additional Notes</label>
                <textarea id="notes" name="notes" rows="4" placeholder="Any additional information about the sale"><?php echo htmlspecialchars($sales['notes'] ?? ''); ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Sale</button>
                <a href="manage_sales.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</main>

<script>
    function calculateTotal() {
        const quantity = parseFloat(document.getElementById('quantity_sold').value) || 0;
        const price = parseFloat(document.getElementById('price_per_unit').value) || 0;
        const total = quantity * price;
        document.getElementById('total_amount').value = total.toFixed(2);
    }
</script>

<?php require_once '../includes/footer.php'; ?>