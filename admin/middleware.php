<?php
// Admin authentication middleware
// File: admin/middleware.php

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Check if user is admin
$user_id = $_SESSION['user_id'];
require_once '../config/db_connection.php';

$query = "SELECT is_admin FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user || $user['is_admin'] != 1) {
    // Not an admin, redirect to regular dashboard
    header('Location: ../dashboard/index.php');
    exit;
}

// User is admin, continue
$_SESSION['is_admin'] = true;
?>
