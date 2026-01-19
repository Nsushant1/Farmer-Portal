<?php
session_start();
session_destroy();
header('Location: ' . (defined('BASE_URL') ? BASE_URL : 'http://localhost/crop-management-portal/') . 'auth/login.php');
exit;
