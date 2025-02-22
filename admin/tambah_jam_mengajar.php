<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    exit;
}

try {
    $guru_id = $_POST['guru_id'];
    $kelas_id = $_POST['kelas_id'];
    $hari = $_POST['hari'];
    $jam = $_POST['jam'];
    
    // Cek jadwal bentrok untuk guru yang sama
    $stmt = $pdo->prepare("
        SELECT jm.*, k.nama_kelas 
        FROM jam_mengajar jm 
        JOIN kelas k ON jm.kelas_id = k.id 
        WHERE jm.guru_id = ? AND jm.hari = ? AND jm.jam = ?
    ");
    $stmt->execute([$guru_id, $hari, $jam]);
    
    if ($stmt->rowCount() > 0) {
        $jadwal_bentrok = $stmt->fetch();
        echo json_encode([
            'success' => false,
            'message' => "Jadwal bentrok! Anda sudah mengajar di kelas {$jadwal_bentrok['nama_kelas']} pada hari {$hari} jam ke-{$jam}"
        ]);
        exit;
    }
    
    // Cek jadwal bentrok untuk kelas yang sama
    $stmt = $pdo->prepare("
        SELECT jm.*, u.nama_lengkap 
        FROM jam_mengajar jm 
        JOIN users u ON jm.guru_id = u.id 
        WHERE jm.kelas_id = ? AND jm.hari = ? AND jm.jam = ?
    ");
    $stmt->execute([$kelas_id, $hari, $jam]);
    
    if ($stmt->rowCount() > 0) {
        $jadwal_bentrok = $stmt->fetch();
        echo json_encode([
            'success' => false,
            'message' => "Jadwal bentrok! Kelas ini sudah memiliki jadwal dengan {$jadwal_bentrok['nama_lengkap']} pada hari {$hari} jam ke-{$jam}"
        ]);
        exit;
    }
    
    // Jika tidak ada bentrok, simpan jadwal
    $stmt = $pdo->prepare("INSERT INTO jam_mengajar (guru_id, kelas_id, hari, jam) VALUES (?, ?, ?, ?)");
    $stmt->execute([$guru_id, $kelas_id, $hari, $jam]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Jadwal berhasil ditambahkan'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan saat menyimpan jadwal'
    ]);
} 