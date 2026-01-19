<?php
$page_title = 'Add New Crop - CropManage';
$css_path = '../assets/style.css';
$base_path = '../';
require_once '../includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $crop_name = mysqli_real_escape_string($conn, $_POST['crop_name'] ?? '');
  $crop_type = mysqli_real_escape_string($conn, $_POST['crop_type'] ?? '');
  $planting_date = $_POST['planting_date'] ?? '';
  $expected_harvest_date = $_POST['expected_harvest_date'] ?? '';
  $status = $_POST['status'] ?? 'Planning';
  $notes = mysqli_real_escape_string($conn, $_POST['notes'] ?? '');

  if (empty($crop_name) || empty($crop_type) || empty($planting_date)) {
    $error = 'Please fill in all required fields';
  } else {
    $query = "INSERT INTO crops (user_id, crop_name, crop_type, planting_date, expected_harvest_date, status, notes)
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'issssss', $user_id, $crop_name, $crop_type, $planting_date, $expected_harvest_date, $status, $notes);

    if (mysqli_stmt_execute($stmt)) {
      header('Location: manage_crops.php?success=1');
      exit;
    } else {
      $error = 'Error adding crop. Please try again.';
    }
    mysqli_stmt_close($stmt);
  }
}

require_once '../includes/navbar.php';
?>

<main class="form-container">
  <div class="form-card">
    <h1>Add New Crop</h1>

    <?php if ($error): ?>
      <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" class="crop-form">
      <div class="form-row">
        <div class="form-group">
          <label for="crop_name">Crop Name *</label>
          <input type="text" id="crop_name" name="crop_name" required placeholder="e.g., Wheat Farm 2025">
        </div>
        <div class="form-group">
          <label for="crop_type">Crop Type *</label>
          <select id="crop_type" name="crop_type" required>
            <option value="">Select Crop Type</option>
            <option value="Wheat">Wheat</option>
            <option value="Rice">Rice</option>
            <option value="Cotton">Cotton</option>
            <option value="Corn">Corn</option>
            <option value="Sugarcane">Sugarcane</option>
            <option value="Pulses">Pulses</option>
            <option value="Vegetables">Vegetables</option>
            <option value="Fruits">Fruits</option>
            <option value="Other">Other</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="planting_date">Planting Date *</label>
          <input type="date" id="planting_date" name="planting_date" required>
        </div>
        <div class="form-group">
          <label for="expected_harvest_date">Expected Harvest Date</label>
          <input type="date" id="expected_harvest_date" name="expected_harvest_date">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="status">Status</label>
          <select id="status" name="status">
            <option value="Planning">Planning</option>
            <option value="Planting">Planting</option>
            <option value="Growing">Growing</option>
            <option value="Ready to Harvest">Ready to Harvest</option>
            <option value="Harvested">Harvested</option>
          </select>
        </div>
      </div>

      <div class="form-group full-width">
        <label for="notes">Additional Notes</label>
        <textarea id="notes" name="notes" placeholder="Add any additional information about this crop"></textarea>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Add Crop</button>
        <a href="manage_crops.php" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</main>

<?php require_once '../includes/footer.php'; ?>