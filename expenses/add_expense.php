<?php
$page_title = 'Add Expense - CropManage';
$css_path = '../assets/style.css';
$base_path = '../';
require_once '../includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $crop_id = intval($_POST['crop_id'] ?? 0);
  $expense_category = mysqli_real_escape_string($conn, $_POST['expense_category'] ?? '');
  $amount = floatval($_POST['amount'] ?? 0);
  $expense_date = $_POST['expense_date'] ?? '';
  $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
  $notes = mysqli_real_escape_string($conn, $_POST['notes'] ?? '');

  if (empty($expense_category) || empty($amount) || empty($expense_date)) {
    $error = 'Please fill in all required fields';
  } else {
    // Allow crop_id to be NULL for general expenses
    $crop_id_value = ($crop_id > 0) ? $crop_id : NULL;

    $query = "INSERT INTO expenses (crop_id, user_id, expense_category, amount, expense_date, description, notes)
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'iisdsss', $crop_id_value, $user_id, $expense_category, $amount, $expense_date, $description, $notes);

    if (mysqli_stmt_execute($stmt)) {
      mysqli_stmt_close($stmt);
      header('Location: manage_expenses.php?success=1');
      exit;
    } else {
      $error = 'Error adding expense. Please try again.';
    }
    mysqli_stmt_close($stmt);
  }
}

// Get user's crops for the dropdown
$crops_query = "SELECT id, crop_name FROM crops WHERE user_id = ? ORDER BY crop_name";
$crops_stmt = mysqli_prepare($conn, $crops_query);
mysqli_stmt_bind_param($crops_stmt, 'i', $user_id);
mysqli_stmt_execute($crops_stmt);
$crops_result = mysqli_stmt_get_result($crops_stmt);

require_once '../includes/navbar.php';
?>

<main class="form-container">
  <div class="form-card">
    <h1>Add Expense</h1>

    <?php if ($error): ?>
      <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" class="expense-form">
      <div class="form-row">
        <div class="form-group">
          <label for="crop_id">Select Crop</label>
          <select id="crop_id" name="crop_id">
            <option value="">General Expense (Not crop-specific)</option>
            <?php while ($crop = mysqli_fetch_assoc($crops_result)): ?>
              <option value="<?php echo $crop['id']; ?>"><?php echo htmlspecialchars($crop['crop_name']); ?></option>
            <?php endwhile; ?>
          </select>
          <small style="color: #666; font-size: 0.85rem;">Optional: Leave blank for general farm expenses</small>
        </div>
        <div class="form-group">
          <label for="expense_category">Expense Category *</label>
          <select id="expense_category" name="expense_category" required>
            <option value="">Select Category</option>
            <option value="Seeds">Seeds</option>
            <option value="Fertilizer">Fertilizer</option>
            <option value="Pesticide">Pesticide</option>
            <option value="Labor">Labor</option>
            <option value="Equipment">Equipment</option>
            <option value="Irrigation">Irrigation</option>
            <option value="Transportation">Transportation</option>
            <option value="Storage">Storage</option>
            <option value="Maintenance">Maintenance</option>
            <option value="Utilities">Utilities</option>
            <option value="Other">Other</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="amount">Amount (Rs.) *</label>
          <input type="number" id="amount" name="amount" required step="0.01" placeholder="e.g., 5000.50" min="0">
        </div>
        <div class="form-group">
          <label for="expense_date">Expense Date *</label>
          <input type="date" id="expense_date" name="expense_date" required max="<?php echo date('Y-m-d'); ?>">
        </div>
      </div>

      <div class="form-group full-width">
        <label for="description">Description</label>
        <textarea id="description" name="description" placeholder="Add details about this expense" rows="3"></textarea>
      </div>

      <div class="form-group full-width">
        <label for="notes">Additional Notes</label>
        <textarea id="notes" name="notes" placeholder="Receipt number, vendor info, or other notes" rows="2"></textarea>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Add Crop</button>
        <a href="manage_expenses.php" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</main>

<?php require_once '../includes/footer.php'; ?>