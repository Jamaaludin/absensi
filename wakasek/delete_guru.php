<?php
require_once '../config/database.php';
require_once '../config/auth.php';
checkRole(['wakasek']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    try {
        $pdo->beginTransaction();
        
        $id = $_POST['id'];
        
        // Cek apakah guru adalah wali kelas
        $stmt = $pdo->prepare("SELECT role, foto FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $guru = $stmt->fetch();
        
        // Hapus foto jika ada
        if (!empty($guru['foto'])) {
            $foto_path = "../assets/img/profile/" . $guru['foto'];
            if (file_exists($foto_path)) {
                unlink($foto_path);
            }
        }
        
        // Hapus data guru
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        
        $pdo->commit();
        $_SESSION['success_msg'] = "Data guru berhasil dihapus!";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_msg'] = "Error: " . $e->getMessage();
    }
}

header("Location: guru.php");
exit;
?> 