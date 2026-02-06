<footer>
  <div class="footer-content">
    <div class="footer-section">
      <h4>About CropManage</h4>
      <p>CropManage is a comprehensive crop management platform designed for modern farmers to maximize productivity and profitability.</p>
    </div>
    <div class="footer-section">
      <h4>Quick Links</h4>
      <ul>
        <li><a href="<?php echo isset($base_path) ? $base_path . 'index.php' : '../index.php'; ?>">Home</a></li>
        <li><a href="<?php echo isset($base_path) ? $base_path . 'index.php#features' : '../index.php#features'; ?>">Features</a></li>
        <li><a href="<?php echo isset($base_path) ? $base_path . 'auth/login.php' : '../auth/login.php'; ?>">Login</a></li>
        <li><a href="<?php echo isset($base_path) ? $base_path . 'auth/register.php' : '../auth/register.php'; ?>">Register</a></li>
      </ul>
    </div>
    <div class="footer-section">
      <h4>Support</h4>
      <ul>
        <li><a href="#">FAQ</a></li>
        <li><a href="#">Help Center</a></li>
        <li><a href="#">Contact Us</a></li>
        <li><a href="#">Documentation</a></li>
      </ul>
    </div>
    <div class="footer-section">
      <h4>Legal</h4>
      <ul>
        <li><a href="#">Privacy Policy</a></li>
        <li><a href="#">Terms of Service</a></li>
        <li><a href="#">Cookie Policy</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <p>&copy; 2025 CropManage - Crop Management Portal. All rights reserved.</p>
  </div>
</footer>

</body>

</html>
<?php
// Close database connection if it exists
if (isset($conn)) {
  mysqli_close($conn);
}
?>