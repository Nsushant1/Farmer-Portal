<?php
$page_title = 'Manage Crops - CropManage';
$css_path = '../assets/style.css';
$base_path = '../';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main class="manage-section">
  <div class="section-header">
    <h1>Manage Crops</h1>
    <a href="add_crop.php" class="btn btn-success">+ Add New Crop</a>
  </div>

  <?php
  // Success message
  if (isset($_GET['success'])) {
    echo '<div class="alert alert-success">Crop added successfully!</div>';
  }
  if (isset($_GET['updated'])) {
    echo '<div class="alert alert-success">Crop updated successfully!</div>';
  }
  if (isset($_GET['deleted'])) {
    echo '<div class="alert alert-success">Crop deleted successfully!</div>';
  }

  $query = "SELECT * FROM crops WHERE user_id = ? ORDER BY created_at DESC";
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
            <th>Crop Name</th>
            <th>Type</th>
            <th>Status</th>
            <th>Planted</th>
            <th>Expected Harvest</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($crop = mysqli_fetch_assoc($result)): ?>
            <tr>
              <td><?php echo htmlspecialchars($crop['crop_name']); ?></td>
              <td><?php echo htmlspecialchars($crop['crop_type']); ?></td>
              <td><span class="badge badge-<?php echo strtolower(str_replace(' ', '-', $crop['status'])); ?>"><?php echo htmlspecialchars($crop['status']); ?></span></td>
              <td><?php echo date('M d, Y', strtotime($crop['planting_date'])); ?></td>
              <td><?php echo $crop['expected_harvest_date'] ? date('M d, Y', strtotime($crop['expected_harvest_date'])) : '-'; ?></td>
              <td class="action-column">
                <a href="edit_crop.php?id=<?php echo $crop['id']; ?>" class="action-btn edit">Edit</a>
                <a href="delete_crop.php?id=<?php echo $crop['id']; ?>" class="action-btn delete" onclick="return confirm('Are you sure you want to delete this crop?');">Delete</a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  <?php
  else:
    echo '<p class="empty-message">No crops added yet. <a href="add_crop.php">Add your first crop</a></p>';
  endif;
  mysqli_stmt_close($stmt);
  ?>
</main>

<?php require_once '../includes/footer.php'; ?>
