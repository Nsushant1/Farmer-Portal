<?php
$is_landing_page = true;
$page_title = 'CropManage - Crop Management Portal';
$css_path = 'assets/style.css';
$base_path = '';

require_once 'includes/header.php';
require_once 'includes/navbar-landing.php';
?>

<!-- Hero Section -->
<section class="hero">
  <div class="hero-content">
    <h1>Smart Crop Management</h1>
    <p>Manage your crops, track expenses, and generate detailed reports - all in one place</p>
    <div class="hero-buttons">
      <?php if (!$isLoggedIn): ?>
        <a href="auth/register.php" class="btn btn-large btn-primary">Get Started</a>
        <a href="auth/login.php" class="btn btn-large btn-secondary">Sign In</a>
      <?php else: ?>
        <a href="dashboard/index.php" class="btn btn-large btn-secondary">Go to Dashboard</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Features Section -->
<section class="features" id="features">
  <div class="features-container">
    <h2 class="section-title">Why Choose CropManage?</h2>
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon">🌾</div>
        <h3>Crop Management</h3>
        <p>Easily add, track, and manage all your crops in one centralized location</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">💰</div>
        <h3>Expense Tracking</h3>
        <p>Monitor all expenses related to your crops and analyze spending patterns</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">📊</div>
        <h3>Detailed Reports</h3>
        <p>Generate comprehensive CSV reports for analysis and record keeping</p>
      </div>
    </div>
  </div>
</section>

<!-- CTA Section -->
<section class="cta" id="about">
  <div class="cta-content">
    <h2>Ready to Transform Your Farming?</h2>
    <p>Join thousands of farmers using CropManage to manage their crops more efficiently and profitably</p>
    <?php if (!$isLoggedIn): ?>
      <a href="auth/register.php" class="btn btn-primary btn-large">Start Your Free Account</a>
    <?php endif; ?>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
