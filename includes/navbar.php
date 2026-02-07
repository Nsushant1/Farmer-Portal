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
        <a href="#" class="btn btn-primary" onclick="showLogoutModal(); return false;">Logout</a>
      </div>
    </ul>
  </div>
</nav>

<!-- Logout Confirmation Modal -->
<div id="logoutModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Confirm Logout</h2>
    </div>
    <div class="modal-body">
      <p>Are you sure you want to logout?</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="hideLogoutModal()">Cancel</button>
      <a href="<?php echo isset($base_path) ? $base_path : '../'; ?>auth/logout.php" class="btn btn-danger">Yes, Logout</a>
    </div>
  </div>
</div>

<style>
  /* Logout Modal Styles */
  .modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    animation: fadeIn 0.3s ease;
  }

  .modal.show {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .modal-content {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    max-width: 400px;
    width: 90%;
    animation: slideDown 0.3s ease;
  }

  .modal-header {
    padding: 20px 25px;
    border-bottom: 1px solid #e0e0e0;
  }

  .modal-header h2 {
    margin: 0;
    color: #2d5016;
    font-size: 22px;
  }

  .modal-body {
    padding: 25px;
  }

  .modal-body p {
    margin: 0;
    color: #333;
    font-size: 16px;
    line-height: 1.6;
  }

  .modal-footer {
    padding: 15px 25px;
    border-top: 1px solid #e0e0e0;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
  }

  .modal-footer .btn {
    padding: 10px 20px;
    font-size: 14px;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
    }

    to {
      opacity: 1;
    }
  }

  @keyframes slideDown {
    from {
      opacity: 0;
      transform: translateY(-20px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
</style>

<script>
  function showLogoutModal() {
    document.getElementById('logoutModal').classList.add('show');
  }

  function hideLogoutModal() {
    document.getElementById('logoutModal').classList.remove('show');
  }

  // Close modal when clicking outside
  window.onclick = function(event) {
    const modal = document.getElementById('logoutModal');
    if (event.target === modal) {
      hideLogoutModal();
    }
  }

  // Close modal with Escape key
  document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
      hideLogoutModal();
    }
  });
</script>