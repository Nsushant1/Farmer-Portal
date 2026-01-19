<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: ../auth/login.php');
  exit;
}

require_once '../config/db_connection.php';

$user_id = $_SESSION['user_id'];
$expense_id = intval($_GET['id'] ?? 0);

if ($expense_id > 0) {
  // Verify the expense belongs to this user before deleting
  $check_query = "SELECT id FROM expenses WHERE id = ? AND user_id = ?";
  $check_stmt = mysqli_prepare($conn, $check_query);
  mysqli_stmt_bind_param($check_stmt, 'ii', $expense_id, $user_id);
  mysqli_stmt_execute($check_stmt);
  $check_result = mysqli_stmt_get_result($check_stmt);

  if (mysqli_num_rows($check_result) > 0) {
    // Delete the expense
    $delete_query = "DELETE FROM expenses WHERE id = ? AND user_id = ?";
    $delete_stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($delete_stmt, 'ii', $expense_id, $user_id);

    if (mysqli_stmt_execute($delete_stmt)) {
      mysqli_stmt_close($delete_stmt);
      mysqli_close($conn);
      header('Location: manage_expenses.php?deleted=1');
      exit;
    }
    mysqli_stmt_close($delete_stmt);
  }
  mysqli_stmt_close($check_stmt);
}

mysqli_close($conn);
header('Location: manage_expenses.php');
exit;
