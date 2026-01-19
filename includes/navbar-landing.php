<nav>
  <div class="nav-wrapper">
    <a href="index.php" class="logo">
      <div class="logo-icon">🌱</div>
      <span>CropManage</span>
    </a>
    <ul>
      <li><a href="#features">Features</a></li>
      <li><a href="#about">About</a></li>
      <div class="auth-buttons">
        <?php if ($isLoggedIn): ?>
          <span style="color: white;">Welcome, <?php echo htmlspecialchars($user_name); ?></span>
          <a href="dashboard/index.php" class="btn btn-primary">Dashboard</a>
          <a href="auth/logout.php" class="btn btn-outline">Logout</a>
        <?php else: ?>
          <a href="auth/login.php" class="btn btn-outline">Login</a>
          <a href="auth/register.php" class="btn btn-primary">Register</a>
        <?php endif; ?>
      </div>
    </ul>
  </div>
</nav>
