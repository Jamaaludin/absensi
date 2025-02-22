<?php
include '../templates/header.php';
require_once '../config/database.php';
checkRole(['wakasek']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        $id = $_POST['id'];
        $username = $_POST['username'];
        $nama_lengkap = $_POST['nama_lengkap'];
        $kelas_id = $_POST['kelas_id'];
        $jenis_kelamin = $_POST['jenis_kelamin'];
        
        // Update data siswa
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET username=?, nama_lengkap=?, kelas_id=?, jenis_kelamin=?, password=? WHERE id=? AND role='siswa'");
            $stmt->execute([$username, $nama_lengkap, $kelas_id, $jenis_kelamin, $password, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username=?, nama_lengkap=?, kelas_id=?, jenis_kelamin=? WHERE id=? AND role='siswa'");
            $stmt->execute([$username, $nama_lengkap, $kelas_id, $jenis_kelamin, $id]);
        }
        
        $pdo->commit();
        $_SESSION['success'] = "Data siswa berhasil diupdate!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

header("Location: siswa.php");
exit();
?> 