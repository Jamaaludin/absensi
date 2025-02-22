<?php
// Pastikan tidak ada output sebelum ini
ob_start();
session_start();

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';

// Lakukan pengecekan login
checkLogin();
?> 