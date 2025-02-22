<?php
require_once '../config/init.php';
checkRole(['nama_role']); // Sesuaikan dengan role yang diizinkan

include '../templates/header.php';
include '../templates/sidebar.php';
require_once '../config/database.php';

// Proses update profil
// ... kode proses update ...

// Ambil data user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Include template profile
include '../templates/profile_template.php';
include '../templates/footer.php';
?> 