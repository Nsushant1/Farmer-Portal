<nav>
  <div class="nav-wrapper">
    <a href="<?php echo isset($base_path) ? $base_path : '../'; ?>dashboard/index.php" class="logo">
      <div class="logo-icon">🌱</div>
      <span>CropManage</span>
    </a>
    <ul>
      <li><a href="<?php echo isset($base_path) ? $base_path : '../'; ?>dashboard/index.php">Dashboard</a></li>
      <li><a href="<?php echo isset($base_path) ? $base_path : '../'; ?>crops/manage_crops.php">Crops</a></li>
      <li><a href="<?php echo isset($base_path) ? $base_path : '../'; ?>expenses/manage_expenses.php">Expenses</a></li>
      <li><a href="<?php echo isset($base_path) ? $base_path : '../'; ?>sales/manage_sales.php">Sales</a></li>
      <li><a href="<?php echo isset($base_path) ? $base_path : '../'; ?>reports/generate_report.php">Reports</a></li>
      <div class="auth-buttons">
        <?php
        $name_parts = explode(' ', $user_name);
        $initials = '';
        foreach ($name_parts as $part) {
          if (!empty($part)) {
            $initials .= strtoupper($part[0]);
          }
        }
        $initials = substr($initials, 0, 2);
        ?>
        <div class="user-avatar" title="<?php echo htmlspecialchars($user_name); ?>"><?php echo $initials; ?></div>
        <a href="<?php echo isset($base_path) ? $base_path : '../'; ?>auth/logout.php" class="btn btn-primary">Logout</a>
      </div>
    </ul>
  </div>
</nav>
