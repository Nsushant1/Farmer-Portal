<?php
$page_title = 'Manage Expenses - CropManage';
$css_path = '../assets/style.css';
$base_path = '../';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main class="manage-section">
  <div class="section-header">
    <h1>Manage Expenses</h1>
    <a href="add_expense.php" class="btn btn-success">+ Add New Expense</a>
  </div>

  <?php
  if (isset($_GET['success'])) {
    echo '<div class="alert alert-success">Expense added successfully!</div>';
  }
  if (isset($_GET['deleted'])) {
    echo '<div class="alert alert-success">Expense deleted successfully!</div>';
  }

  $query = "SELECT e.*, c.crop_name
            FROM expenses e
            LEFT JOIN crops c ON e.crop_id = c.id
            WHERE e.user_id = ?
            ORDER BY e.expense_date DESC";
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
            <th>Date</th>
            <th>Crop</th>
            <th>Category</th>
            <th>Description</th>
            <th>Amount</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $total = 0;
          while ($expense = mysqli_fetch_assoc($result)):
            $total += $expense['amount'];
          ?>
            <tr>
              <td><?php echo date('M d, Y', strtotime($expense['expense_date'])); ?></td>
              <td><?php echo $expense['crop_name'] ? htmlspecialchars($expense['crop_name']) : '<em>General</em>'; ?></td>
              <td><?php echo htmlspecialchars($expense['expense_category']); ?></td>
              <td><?php echo htmlspecialchars($expense['description'] ?: '-'); ?></td>
              <td class="amount">Rs. <?php echo number_format($expense['amount'], 2); ?></td>
              <td class="action-column">
                <a href="delete_expense.php?id=<?php echo $expense['id']; ?>" class="action-btn delete" onclick="return confirm('Are you sure you want to delete this expense?');">Delete</a>
              </td>
            </tr>
          <?php endwhile; ?>
          <tr style="background: #f0f0f0; font-weight: bold;">
            <td colspan="4" style="text-align: right;">Total Expenses:</td>
            <td class="amount">Rs. <?php echo number_format($total, 2); ?></td>
            <td></td>
          </tr>
        </tbody>
      </table>
    </div>
  <?php
  else:
    echo '<p class="empty-message">No expenses added yet. <a href="add_expense.php">Add your first expense</a></p>';
  endif;
  mysqli_stmt_close($stmt);
  ?>
</main>

<?php require_once '../includes/footer.php'; ?>
