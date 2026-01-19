<?php
$page_title = 'Add Sale - CropManage';
$css_path = '../assets/style.css';
$base_path = '../';
require_once '../includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $query = "INSERT INTO sales (crop_id, user_id, sale_date, quantity_sold, quantity_unit, price_per_unit, total_amount, notes)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'iisdsdds', $crop_id, $user_id, $sale_date, $quantity_sold, $quantity_unit, $price_per_unit, $total_amount, $notes);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header('Location: manage_sales.php?success=1');
            exit;
        } else {
            $error = 'Error adding sale. Please try again.';
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
        <h1>Add New Sale</h1>

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
                            <option value="<?php echo $crop['id']; ?>"><?php echo htmlspecialchars($crop['crop_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="sale_date">Sale Date *</label>
                    <input type="date" id="sale_date" name="sale_date" required max="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="quantity_sold">Quantity Sold *</label>
                    <input type="number" id="quantity_sold" name="quantity_sold" required step="0.01" min="0" placeholder="e.g., 100" oninput="calculateTotal()">
                </div>
                <div class="form-group">
                    <label for="quantity_unit">Unit *</label>
                    <select id="quantity_unit" name="quantity_unit" required>
                        <option value="kg">Kilograms (kg)</option>
                        <option value="quintal">Quintal</option>
                        <option value="ton">Ton</option>
                        <option value="bag">Bag</option>
                        <option value="piece">Piece</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="price_per_unit">Price Per Unit (Rs.) *</label>
                    <input type="number" id="price_per_unit" name="price_per_unit" required step="0.01" min="0" placeholder="e.g., 50.00" oninput="calculateTotal()">
                </div>
                <div class="form-group">
                    <label for="total_amount">Total Amount (Rs.)</label>
                    <input type="number" id="total_amount" step="0.01" readonly style="background: #f5f5f5;" placeholder="Auto-calculated">
                </div>
            </div>

            <div class="form-group full-width">
                <label for="notes">Additional Notes</label>
                <textarea id="notes" name="notes" rows="4" placeholder="Any additional information about the sale"></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add Sale</button>
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
