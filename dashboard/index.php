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
        <a href="../crops/add_crop.php" class="btn btn-success">
          <i class="fa fa-plus-circle"></i> Add New Crop
        </a>
        <a href="../expenses/add_expense.php" class="btn btn-primary">
          <i class="fa fa-plus-circle"></i> Add Expense
        </a>
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
        <div class="stat-icon-wrapper" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
          <i class="fa fa-leaf"></i>
        </div>
        <div class="stat-content">
          <h3 style="color: #666; font-weight: 600; font-size: 13px;">Total Crops</h3>
          <p class="stat-value" style="color: #333; font-size: 32px; font-weight: 700; margin: 5px 0 0 0;">
            <?php echo $crops_data['total_crops'] ?? 0; ?>
          </p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon-wrapper" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
          <i class="fa fa-wallet"></i>
        </div>
        <div class="stat-content">
          <h3 style="color: #666; font-weight: 600; font-size: 13px;">Total Expenses</h3>
          <p class="stat-value" style="color: #333; font-size: 28px; font-weight: 700; margin: 5px 0 0 0;">
            Rs. <?php echo number_format($expenses_data['total_expenses'] ?? 0, 2); ?>
          </p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon-wrapper" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
          <i class="fa fa-seedling"></i>
        </div>
        <div class="stat-content">
          <h3 style="color: #666; font-weight: 600; font-size: 13px;">Active Crops</h3>
          <p class="stat-value" style="color: #333; font-size: 32px; font-weight: 700; margin: 5px 0 0 0;">
            <?php echo $active_data['active_crops'] ?? 0; ?>
          </p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon-wrapper" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
          <i class="fa fa-chart-line"></i>
        </div>
        <div class="stat-content">
          <h3 style="color: #666; font-weight: 600; font-size: 13px;">Quick Actions</h3>
          <p style="margin: 5px 0 0 0;">
            <a href="../reports/generate_report.php" class="action-link" style="color: #38f9d7; font-weight: 600; text-decoration: none; font-size: 14px;">
              <i class="fa fa-file-alt" style="margin-right: 5px;"></i> Generate Report
            </a>
          </p>
        </div>
      </div>
    </section>

    <section class="recent-crops">
      <h2 style="color: #333; font-weight: 600; font-size: 22px; margin-bottom: 20px;">
        <i class="fa fa-history" style="color: #667eea; margin-right: 8px;"></i> Recent Crops
      </h2>
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
                <th style="color: #333; font-size: 14px;"><i class="fa fa-leaf" style="margin-right: 5px; color: #667eea;"></i> Crop Name</th>
                <th style="color: #333; font-size: 14px;"><i class="fa fa-tag" style="margin-right: 5px; color: #667eea;"></i> Type</th>
                <th style="color: #333; font-size: 14px;"><i class="fa fa-info-circle" style="margin-right: 5px; color: #667eea;"></i> Status</th>
                <th style="color: #333; font-size: 14px;"><i class="fa fa-calendar" style="margin-right: 5px; color: #667eea;"></i> Planted</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($crop = mysqli_fetch_assoc($recent_result)): ?>
                <tr>
                  <td style="color: #333; font-weight: 500;"><?php echo htmlspecialchars($crop['crop_name']); ?></td>
                  <td style="color: #666;"><?php echo htmlspecialchars($crop['crop_type']); ?></td>
                  <td>
                    <span class="badge badge-<?php echo strtolower($crop['status']); ?>">
                      <?php echo ucfirst($crop['status']); ?>
                    </span>
                  </td>
                  <td style="color: #666;"><?php echo date('M d, Y', strtotime($crop['planting_date'])); ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php
      else:
        echo '<p class="empty-message" style="color: #666; padding: 30px; text-align: center;">
                <i class="fa fa-info-circle" style="margin-right: 5px;"></i> No crops yet. 
                <a href="../crops/add_crop.php" style="color: #667eea; font-weight: 600;">
                  <i class="fa fa-plus-circle" style="margin-left: 5px;"></i> Add your first crop
                </a>
              </p>';
      endif;
      mysqli_stmt_close($stmt);
      ?>
    </section>
  </main>
</div>

<style>
  .stat-icon-wrapper {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .stat-icon-wrapper i {
    font-size: 22px;
    color: white;
  }

  .stat-card {
    transition: all 0.3s ease;
  }

  .stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
  }
</style>

<?php require_once '../includes/footer.php'; ?>