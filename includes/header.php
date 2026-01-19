<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Check if user is logged in (only for dashboard pages, not landing page)
if (!isset($is_landing_page) && !isset($_SESSION['user_id'])) {
  header('Location: ../auth/login.php');
  exit;
}

// Only require database for dashboard pages
if (!isset($is_landing_page)) {
  require_once __DIR__ . '/../config/db_connection.php';
}

// Get user info
$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['user_name'] ?? 'User';
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $page_title ?? 'CropManage - Crop Management Portal'; ?></title>
  <link rel="stylesheet" href="<?php echo $css_path ?? '../assets/style.css'; ?>">
</head>

<body>
