<?php
$page_title = 'Edit Crop - CropManage';
$css_path = '../assets/style.css';
$base_path = '../';
require_once '../includes/header.php';

$crop_id = intval($_GET['id'] ?? 0);
$error = '';
$success = '';

// Fetch crop data
$query = "SELECT * FROM crops WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $crop_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
  header('Location: manage_crops.php');
  exit;
}

$crop = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

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
    $update_query = "UPDATE crops SET crop_name = ?, crop_type = ?, planting_date = ?, expected_harvest_date = ?, status = ?, notes = ? WHERE id = ? AND user_id = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, 'ssssssii', $crop_name, $crop_type, $planting_date, $expected_harvest_date, $status, $notes, $crop_id, $user_id);

    if (mysqli_stmt_execute($update_stmt)) {
      mysqli_stmt_close($update_stmt);
      header('Location: manage_crops.php?updated=1');
      exit;
    } else {
      $error = 'Error updating crop. Please try again.';
    }
    mysqli_stmt_close($update_stmt);
  }
}

require_once '../includes/navbar.php';
?>

<main class="form-container">
  <div class="form-card">
    <h1>Edit Crop</h1>

    <?php if ($error): ?>
      <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" class="crop-form">
      <div class="form-row">
        <div class="form-group">
          <label for="crop_name">Crop Name *</label>
          <input type="text" id="crop_name" name="crop_name" required value="<?php echo htmlspecialchars($crop['crop_name']); ?>">
        </div>
        <div class="form-group">
          <label for="crop_type">Crop Type *</label>
          <select id="crop_type" name="crop_type" required>
            <option value="Wheat" <?php echo $crop['crop_type'] === 'Wheat' ? 'selected' : ''; ?>>Wheat</option>
            <option value="Rice" <?php echo $crop['crop_type'] === 'Rice' ? 'selected' : ''; ?>>Rice</option>
            <option value="Cotton" <?php echo $crop['crop_type'] === 'Cotton' ? 'selected' : ''; ?>>Cotton</option>
            <option value="Corn" <?php echo $crop['crop_type'] === 'Corn' ? 'selected' : ''; ?>>Corn</option>
            <option value="Sugarcane" <?php echo $crop['crop_type'] === 'Sugarcane' ? 'selected' : ''; ?>>Sugarcane</option>
            <option value="Pulses" <?php echo $crop['crop_type'] === 'Pulses' ? 'selected' : ''; ?>>Pulses</option>
            <option value="Vegetables" <?php echo $crop['crop_type'] === 'Vegetables' ? 'selected' : ''; ?>>Vegetables</option>
            <option value="Fruits" <?php echo $crop['crop_type'] === 'Fruits' ? 'selected' : ''; ?>>Fruits</option>
            <option value="Other" <?php echo $crop['crop_type'] === 'Other' ? 'selected' : ''; ?>>Other</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="planting_date">Planting Date *</label>
          <input type="date" id="planting_date" name="planting_date" required value="<?php echo htmlspecialchars($crop['planting_date']); ?>">
        </div>
        <div class="form-group">
          <label for="expected_harvest_date">Expected Harvest Date</label>
          <input type="date" id="expected_harvest_date" name="expected_harvest_date" value="<?php echo htmlspecialchars($crop['expected_harvest_date'] ?? ''); ?>">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="status">Status</label>
          <select id="status" name="status">
            <option value="Planning" <?php echo $crop['status'] === 'Planning' ? 'selected' : ''; ?>>Planning</option>
            <option value="Planting" <?php echo $crop['status'] === 'Planting' ? 'selected' : ''; ?>>Planting</option>
            <option value="Growing" <?php echo $crop['status'] === 'Growing' ? 'selected' : ''; ?>>Growing</option>
            <option value="Ready to Harvest" <?php echo $crop['status'] === 'Ready to Harvest' ? 'selected' : ''; ?>>Ready to Harvest</option>
            <option value="Harvested" <?php echo $crop['status'] === 'Harvested' ? 'selected' : ''; ?>>Harvested</option>
          </select>
        </div>
      </div>

      <div class="form-group full-width">
        <label for="notes">Additional Notes</label>
        <textarea id="notes" name="notes"><?php echo htmlspecialchars($crop['notes'] ?? ''); ?></textarea>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Update Crop</button>
        <a href="manage_crops.php" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</main>

<?php require_once '../includes/footer.php'; ?>
