<?php
require_once '../config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        $id = $_POST['id'];
        $guru_id = $_POST['guru_id'];
        $mapel = $_POST['mapel'];

        // Hapus jadwal
        $stmt = $pdo->prepare("DELETE FROM jam_mengajar WHERE id = ?");
        $stmt->execute([$id]);

        $pdo->commit();
        $_SESSION['success'] = "Jadwal berhasil dihapus!";

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }

    // Redirect kembali ke halaman kelola jadwal
    header("Location: kelola_jam_mengajar.php?guru_id=" . $guru_id . "&mapel=" . urlencode($mapel));
    exit;
}

// Jika bukan POST, redirect ke halaman guru
header("Location: guru.php");
exit; 