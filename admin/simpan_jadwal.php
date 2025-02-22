<?php
require_once '../config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Ambil data dari form
        $guru_id = $_POST['guru_id'];
        $mata_pelajaran = $_POST['mata_pelajaran'];
        $hari = $_POST['hari'];
        $jam = $_POST['jam'];
        $kelas_id = $_POST['kelas_id'];

        // Cek jadwal bentrok
        $stmt = $pdo->prepare("SELECT jm.*, u.nama_lengkap as nama_guru 
                              FROM jam_mengajar jm 
                              JOIN users u ON jm.guru_id = u.id 
                              WHERE jm.kelas_id = ? AND jm.hari = ? AND jm.jam = ? AND jm.guru_id != ?");
        $stmt->execute([$kelas_id, $hari, $jam, $guru_id]);
        $bentrok = $stmt->fetch();

        if ($bentrok) {
            throw new Exception("Jadwal bentrok dengan " . $bentrok['nama_guru']);
        }

        // Insert jadwal baru
        $stmt = $pdo->prepare("INSERT INTO jam_mengajar (guru_id, kelas_id, hari, jam, mata_pelajaran) 
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$guru_id, $kelas_id, $hari, $jam, $mata_pelajaran]);

        $pdo->commit();
        $_SESSION['success'] = "Jadwal berhasil ditambahkan!";

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

// Redirect kembali ke halaman kelola jadwal
header("Location: kelola_jam_mengajar.php?guru_id=" . $guru_id . "&mapel=" . urlencode($mata_pelajaran));
exit; 