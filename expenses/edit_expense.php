<?php
require_once '../config/db_connection.php';
require_once '../includes/header.php';

$expense_id = intval($_GET['id'] ?? 0);
$error = '';
$success = '';

// Fetch expense data
$query = "SELECT * FROM expenses WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $expense_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
  header('Location: ' . BASE_URL . 'expenses/manage_expenses.php');
  exit;
}

$expense = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $crop_id = intval($_POST['crop_id'] ?? 0);
  $expense_category = mysqli_real_escape_string($conn, $_POST['expense_category'] ?? '');
  $amount = floatval($_POST['amount'] ?? 0);
  $expense_date = $_POST['expense_date'] ?? '';
  $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
  $receipt_notes = mysqli_real_escape_string($conn, $_POST['receipt_notes'] ?? '');

  if (empty($crop_id) || empty($expense_category) || empty($amount) || empty($expense_date)) {
    $error = 'Please fill in all required fields';
  } else {
    $update_query = "UPDATE expenses SET crop_id = ?, expense_category = ?, amount = ?, expense_date = ?, description = ?, receipt_notes = ? WHERE id = ? AND user_id = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, 'isdsssii', $crop_id, $expense_category, $amount, $expense_date, $description, $receipt_notes, $expense_id, $user_id);

    if (mysqli_stmt_execute($update_stmt)) {
      $success = 'Expense updated successfully!';
      header('Location: ' . BASE_URL . 'expenses/manage_expenses.php?success=1');
      exit;
    } else {
      $error = 'Error updating expense. Please try again.';
    }
    mysqli_stmt_close($update_stmt);
  }
}

// Get user's crops for dropdown
$crops_query = "SELECT id, crop_name FROM crops WHERE user_id = ? ORDER BY crop_name";
$crops_stmt = mysqli_prepare($conn, $crops_query);
mysqli_stmt_bind_param($crops_stmt, 'i', $user_id);
mysqli_stmt_execute($crops_stmt);
$crops_result = mysqli_stmt_get_result($crops_stmt);
?>

<?php require_once '../includes/navbar.php'; ?>

<main class="form-container">
  <div class="form-card">
    <h1>Edit Expense</h1>

    <?php if ($error): ?>
      <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" class="expense-form">
      <div class="form-row">
        <div class="form-group">
          <label for="crop_id">Select Crop *</label>
          <select id="crop_id" name="crop_id" required>
            <option value="">Choose a crop</option>
            <?php while ($crop = mysqli_fetch_assoc($crops_result)): ?>
              <option value="<?php echo $crop['id']; ?>" <?php echo $crop['id'] === $expense['crop_id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($crop['crop_name']); ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="expense_category">Expense Category *</label>
          <select id="expense_category" name="expense_category" required>
            <option value="Seeds" <?php echo $expense['expense_category'] === 'Seeds' ? 'selected' : ''; ?>>Seeds</option>
            <option value="Fertilizer" <?php echo $expense['expense_category'] === 'Fertilizer' ? 'selected' : ''; ?>>Fertilizer</option>
            <option value="Pesticide" <?php echo $expense['expense_category'] === 'Pesticide' ? 'selected' : ''; ?>>Pesticide</option>
            <option value="Labor" <?php echo $expense['expense_category'] === 'Labor' ? 'selected' : ''; ?>>Labor</option>
            <option value="Equipment" <?php echo $expense['expense_category'] === 'Equipment' ? 'selected' : ''; ?>>Equipment</option>
            <option value="Irrigation" <?php echo $expense['expense_category'] === 'Irrigation' ? 'selected' : ''; ?>>Irrigation</option>
            <option value="Transportation" <?php echo $expense['expense_category'] === 'Transportation' ? 'selected' : ''; ?>>Transportation</option>
            <option value="Storage" <?php echo $expense['expense_category'] === 'Storage' ? 'selected' : ''; ?>>Storage</option>
            <option value="Other" <?php echo $expense['expense_category'] === 'Other' ? 'selected' : ''; ?>>Other</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="amount">Amount (₹) *</label>
          <input type="number" id="amount" name="amount" required step="0.01" value="<?php echo $expense['amount']; ?>">
        </div>
        <div class="form-group">
          <label for="expense_date">Expense Date *</label>
          <input type="date" id="expense_date" name="expense_date" required value="<?php echo $expense['expense_date']; ?>">
        </div>
      </div>

      <div class="form-group full-width">
        <label for="description">Description</label>
        <textarea id="description" name="description"><?php echo htmlspecialchars($expense['description'] ?? ''); ?></textarea>
      </div>

      <div class="form-group full-width">
        <label for="receipt_notes">Receipt Notes</label>
        <input type="text" id="receipt_notes" name="receipt_notes" value="<?php echo htmlspecialchars($expense['receipt_notes'] ?? ''); ?>">
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Update Expense</button>
        <a href="<?php echo BASE_URL; ?>expenses/manage_expenses.php" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</main>

<?php require_once '../includes/footer.php'; ?>
