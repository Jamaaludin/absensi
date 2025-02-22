<?php
include '../templates/header.php';
require_once '../config/database.php';
checkRole(['wakasek']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    try {
        $pdo->beginTransaction();
        
        $id = $_POST['id'];
        
        // Ambil info foto sebelum menghapus
        $stmt = $pdo->prepare("SELECT foto FROM users WHERE id = ? AND role = 'siswa'");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        // Hapus data siswa
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'siswa'");
        $stmt->execute([$id]);
        
        // Hapus foto jika ada
        if (!empty($user['foto'])) {
            $foto_path = '../assets/img/profile/' . $user['foto'];
            if (file_exists($foto_path)) {
                unlink($foto_path);
            }
        }
        
        $pdo->commit();
        $_SESSION['success'] = "Data siswa berhasil dihapus!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

header("Location: siswa.php");
exit();
?> 