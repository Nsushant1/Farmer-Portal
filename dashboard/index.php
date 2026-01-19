<?php
$page_title = 'Dashboard - CropManage';
$css_path = '../assets/style.css';
$base_path = '../';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container">
  <main class="dashboard">
    <section class="dashboard-header">
      <div class="welcome-section">
        <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
        <p>Track and manage your crops efficiently</p>
      </div>
      <div class="quick-actions">
        <a href="../crops/add_crop.php" class="btn btn-success">+ Add New Crop</a>
        <a href="../expenses/add_expense.php" class="btn btn-primary">+ Add Expense</a>
      </div>
    </section>

    <section class="dashboard-stats">
      <?php
      // Get crop statistics
      $crops_query = "SELECT COUNT(*) as total_crops FROM crops WHERE user_id = ?";
      $stmt = mysqli_prepare($conn, $crops_query);
      mysqli_stmt_bind_param($stmt, 'i', $user_id);
      mysqli_stmt_execute($stmt);
      $crops_result = mysqli_stmt_get_result($stmt);
      $crops_data = mysqli_fetch_assoc($crops_result);
      mysqli_stmt_close($stmt);

      // Get total expenses
      $expenses_query = "SELECT SUM(amount) as total_expenses FROM expenses WHERE user_id = ?";
      $stmt = mysqli_prepare($conn, $expenses_query);
      mysqli_stmt_bind_param($stmt, 'i', $user_id);
      mysqli_stmt_execute($stmt);
      $expenses_result = mysqli_stmt_get_result($stmt);
      $expenses_data = mysqli_fetch_assoc($expenses_result);
      mysqli_stmt_close($stmt);

      // Get active crops
      $active_query = "SELECT COUNT(*) as active_crops FROM crops WHERE user_id = ? AND status IN ('Planning', 'Planting', 'Growing')";
      $stmt = mysqli_prepare($conn, $active_query);
      mysqli_stmt_bind_param($stmt, 'i', $user_id);
      mysqli_stmt_execute($stmt);
      $active_result = mysqli_stmt_get_result($stmt);
      $active_data = mysqli_fetch_assoc($active_result);
      mysqli_stmt_close($stmt);
      ?>

      <div class="stat-card">
        <div class="stat-icon">📊</div>
        <div class="stat-content">
          <h3>Total Crops</h3>
          <p class="stat-value"><?php echo $crops_data['total_crops'] ?? 0; ?></p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-content">
          <h3>Total Expenses</h3>
          <p class="stat-value">Rs. <?php echo number_format($expenses_data['total_expenses'] ?? 0, 2); ?></p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">🌱</div>
        <div class="stat-content">
          <h3>Active Crops</h3>
          <p class="stat-value"><?php echo $active_data['active_crops'] ?? 0; ?></p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">📈</div>
        <div class="stat-content">
          <h3>Quick Actions</h3>
          <p><a href="../reports/generate_report.php" class="action-link">Generate Report</a></p>
        </div>
      </div>
    </section>

    <section class="recent-crops">
      <h2>Recent Crops</h2>
      <?php
      $recent_query = "SELECT * FROM crops WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
      $stmt = mysqli_prepare($conn, $recent_query);
      mysqli_stmt_bind_param($stmt, 'i', $user_id);
      mysqli_stmt_execute($stmt);
      $recent_result = mysqli_stmt_get_result($stmt);

      if (mysqli_num_rows($recent_result) > 0):
      ?>
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Crop Name</th>
                <th>Type</th>
                <th>Status</th>
                <th>Planted</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($crop = mysqli_fetch_assoc($recent_result)): ?>
                <tr>
                  <td><?php echo htmlspecialchars($crop['crop_name']); ?></td>
                  <td><?php echo htmlspecialchars($crop['crop_type']); ?></td>
                  <td>
                    <span class="badge badge-<?php echo strtolower($crop['status']); ?>">
                      <?php echo ucfirst($crop['status']); ?>
                    </span>
                  </td>
                  <td><?php echo date('M d, Y', strtotime($crop['planting_date'])); ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php
      else:
        echo '<p class="empty-message">No crops yet. <a href="../crops/add_crop.php">Add your first crop</a></p>';
      endif;
      mysqli_stmt_close($stmt);
      ?>
    </section>
  </main>
</div>

<?php require_once '../includes/footer.php'; ?>
