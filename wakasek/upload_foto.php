<?php
include '../templates/header.php';
require_once '../config/database.php';
checkRole(['wakasek']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        if (empty($_FILES['foto']['name'])) {
            throw new Exception('File foto tidak ditemukan!');
        }

        $foto = $_FILES['foto'];
        $nis = $_POST['nis'];
        $ext = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));
        
        // Validasi tipe file
        if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
            throw new Exception('Tipe file tidak diizinkan. Hanya JPG, JPEG, dan PNG yang diperbolehkan.');
        }

        // Generate nama file baru
        $new_filename = 'siswa_' . $nis . '_' . time() . '.' . $ext;
        $upload_dir = '../assets/img/profile/';
        
        // Buat direktori jika belum ada
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Upload file
        if (move_uploaded_file($foto['tmp_name'], $upload_dir . $new_filename)) {
            // Update foto di database
            $stmt = $pdo->prepare("UPDATE users SET foto = ? WHERE username = ? AND role = 'siswa'");
            $stmt->execute([$new_filename, $nis]);
            
            $pdo->commit();
            $_SESSION['success'] = "Foto berhasil diupload!";
        } else {
            throw new Exception('Gagal mengupload file. Silakan coba lagi.');
        }
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = $e->getMessage();
    }
}

header("Location: siswa.php");
exit();
?> 