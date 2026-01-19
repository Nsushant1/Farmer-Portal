<?php
$page_title = 'Generate Reports - CropManage';
$css_path = '../assets/style.css';
$base_path = '../';
require_once '../includes/header.php';

$crop_filter = $_GET['crop_id'] ?? '';

require_once '../includes/navbar.php';
?>

<main class="manage-section">
  <div class="section-header">
    <h1>Profit & Loss Report</h1>
  </div>

  <div class="report-filters">
    <h3>Report Options</h3>
    <form method="GET" class="filter-form">
      <div class="form-group">
        <label for="crop_id">Filter by Crop (Optional)</label>
        <select id="crop_id" name="crop_id">
          <option value="">All Crops</option>
          <?php
          $crops_query = "SELECT id, crop_name FROM crops WHERE user_id = ? ORDER BY crop_name";
          $crops_stmt = mysqli_prepare($conn, $crops_query);
          mysqli_stmt_bind_param($crops_stmt, 'i', $user_id);
          mysqli_stmt_execute($crops_stmt);
          $crops_result = mysqli_stmt_get_result($crops_stmt);

          while ($crop = mysqli_fetch_assoc($crops_result)):
          ?>
            <option value="<?php echo $crop['id']; ?>" <?php echo $crop_filter == $crop['id'] ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($crop['crop_name']); ?>
            </option>
          <?php endwhile;
          mysqli_stmt_close($crops_stmt);
          ?>
        </select>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Generate Report</button>
      </div>
    </form>
  </div>

  <div class="report-preview">
    <h3>Financial Report</h3>
    <?php
    // Build query with expenses and sales
    if (!empty($crop_filter)) {
      $report_query = "SELECT
                          c.id,
                          c.crop_name,
                          c.crop_type,
                          c.planting_date,
                          c.expected_harvest_date,
                          c.status,
                          COALESCE(SUM(e.amount), 0) as total_expenses,
                          COUNT(DISTINCT e.id) as expense_count,
                          COALESCE(SUM(s.total_amount), 0) as total_sales,
                          COUNT(DISTINCT s.id) as sales_count
                       FROM crops c
                       LEFT JOIN expenses e ON c.id = e.crop_id
                       LEFT JOIN sales s ON c.id = s.crop_id
                       WHERE c.user_id = ? AND c.id = ?
                       GROUP BY c.id";
      $stmt = mysqli_prepare($conn, $report_query);
      mysqli_stmt_bind_param($stmt, 'ii', $user_id, $crop_filter);
    } else {
      $report_query = "SELECT
                          c.id,
                          c.crop_name,
                          c.crop_type,
                          c.planting_date,
                          c.expected_harvest_date,
                          c.status,
                          COALESCE(SUM(e.amount), 0) as total_expenses,
                          COUNT(DISTINCT e.id) as expense_count,
                          COALESCE(SUM(s.total_amount), 0) as total_sales,
                          COUNT(DISTINCT s.id) as sales_count
                       FROM crops c
                       LEFT JOIN expenses e ON c.id = e.crop_id
                       LEFT JOIN sales s ON c.id = s.crop_id
                       WHERE c.user_id = ?
                       GROUP BY c.id";
      $stmt = mysqli_prepare($conn, $report_query);
      mysqli_stmt_bind_param($stmt, 'i', $user_id);
    }

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
              <th>Planted</th>
              <th>Harvest Date</th>
              <th>Status</th>
              <th>Total Expenses</th>
              <th>Total Sales</th>
              <th>Profit/Loss</th>
              <th>Margin %</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $grand_total_expenses = 0;
            $grand_total_sales = 0;
            $grand_total_profit = 0;

            while ($row = mysqli_fetch_assoc($result)):
              $total_expenses = $row['total_expenses'];
              $total_sales = $row['total_sales'];
              $profit_loss = $total_sales - $total_expenses;
              $margin = $total_sales > 0 ? ($profit_loss / $total_sales) * 100 : 0;

              $grand_total_expenses += $total_expenses;
              $grand_total_sales += $total_sales;
              $grand_total_profit += $profit_loss;
            ?>
              <tr>
                <td><?php echo htmlspecialchars($row['crop_name']); ?></td>
                <td><?php echo htmlspecialchars($row['crop_type']); ?></td>
                <td><?php echo date('M d, Y', strtotime($row['planting_date'])); ?></td>
                <td><?php echo $row['expected_harvest_date'] ? date('M d, Y', strtotime($row['expected_harvest_date'])) : 'N/A'; ?></td>
                <td><span class="badge badge-<?php echo strtolower(str_replace(' ', '-', $row['status'])); ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                <td class="amount" style="color: #d32f2f;">Rs. <?php echo number_format($total_expenses, 2); ?></td>
                <td class="amount" style="color: #388e3c;">Rs. <?php echo number_format($total_sales, 2); ?></td>
                <td class="amount" style="color: <?php echo $profit_loss >= 0 ? '#388e3c' : '#d32f2f'; ?>; font-weight: bold;">
                  Rs. <?php echo number_format($profit_loss, 2); ?>
                </td>
                <td style="color: <?php echo $margin >= 0 ? '#388e3c' : '#d32f2f'; ?>; font-weight: bold;">
                  <?php echo number_format($margin, 2); ?>%
                </td>
              </tr>
            <?php endwhile;

            $grand_margin = $grand_total_sales > 0 ? ($grand_total_profit / $grand_total_sales) * 100 : 0;
            ?>
          </tbody>
          <tfoot>
            <tr style="font-weight: bold; background: #f5f5f5; border-top: 2px solid #333;">
              <td colspan="5" style="text-align: right; font-size: 1.1em;">GRAND TOTALS:</td>
              <td class="amount" style="color: #d32f2f; font-size: 1.1em;">Rs. <?php echo number_format($grand_total_expenses, 2); ?></td>
              <td class="amount" style="color: #388e3c; font-size: 1.1em;">Rs. <?php echo number_format($grand_total_sales, 2); ?></td>
              <td class="amount" style="color: <?php echo $grand_total_profit >= 0 ? '#388e3c' : '#d32f2f'; ?>; font-size: 1.1em;">
                Rs. <?php echo number_format($grand_total_profit, 2); ?>
              </td>
              <td style="color: <?php echo $grand_margin >= 0 ? '#388e3c' : '#d32f2f'; ?>; font-size: 1.1em;">
                <?php echo number_format($grand_margin, 2); ?>%
              </td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Summary Cards -->
      <div class="summary-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 30px;">
        <div class="summary-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-left: 4px solid #d32f2f;">
          <h4 style="margin: 0 0 10px 0; color: #666; font-size: 0.9em;">Total Expenses</h4>
          <p style="margin: 0; font-size: 1.8em; font-weight: bold; color: #d32f2f;">Rs. <?php echo number_format($grand_total_expenses, 2); ?></p>
        </div>

        <div class="summary-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-left: 4px solid #388e3c;">
          <h4 style="margin: 0 0 10px 0; color: #666; font-size: 0.9em;">Total Sales Revenue</h4>
          <p style="margin: 0; font-size: 1.8em; font-weight: bold; color: #388e3c;">Rs. <?php echo number_format($grand_total_sales, 2); ?></p>
        </div>

        <div class="summary-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-left: 4px solid <?php echo $grand_total_profit >= 0 ? '#388e3c' : '#d32f2f'; ?>;">
          <h4 style="margin: 0 0 10px 0; color: #666; font-size: 0.9em;">Net Profit/Loss</h4>
          <p style="margin: 0; font-size: 1.8em; font-weight: bold; color: <?php echo $grand_total_profit >= 0 ? '#388e3c' : '#d32f2f'; ?>;">
            Rs. <?php echo number_format($grand_total_profit, 2); ?>
          </p>
        </div>

        <div class="summary-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-left: 4px solid #1976d2;">
          <h4 style="margin: 0 0 10px 0; color: #666; font-size: 0.9em;">Profit Margin</h4>
          <p style="margin: 0; font-size: 1.8em; font-weight: bold; color: <?php echo $grand_margin >= 0 ? '#388e3c' : '#d32f2f'; ?>;">
            <?php echo number_format($grand_margin, 2); ?>%
          </p>
        </div>
      </div>

    <?php
    else:
      echo '<p class="empty-message">No crop data available for report. <a href="../crops/add_crop.php">Add a crop first</a></p>';
    endif;
    mysqli_stmt_close($stmt);
    ?>
  </div>
</main>

<?php require_once '../includes/footer.php'; ?>