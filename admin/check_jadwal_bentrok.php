<?php
require_once '../config/database.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

$kelas_id = $_POST['kelas_id'] ?? null;
$hari = $_POST['hari'] ?? null;
$jam = $_POST['jam'] ?? null;
$guru_id = $_POST['guru_id'] ?? null;

if (!$kelas_id || !$hari || !$jam) {
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

try {
    // Cek jadwal bentrok untuk kelas yang sama
    $sql = "SELECT jm.*, u.nama_lengkap as nama_guru, k.nama_kelas 
            FROM jam_mengajar jm 
            JOIN users u ON jm.guru_id = u.id 
            JOIN kelas k ON jm.kelas_id = k.id
            WHERE jm.kelas_id = ? AND jm.hari = ? AND jm.jam = ?";
    
    $params = [$kelas_id, $hari, $jam];
    
    if ($guru_id) {
        $sql .= " AND jm.guru_id != ?";
        $params[] = $guru_id;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $bentrok = $stmt->fetch();
    
    $hari_list = [
        1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
        4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'
    ];
    
    if ($bentrok) {
        echo json_encode([
            'bentrok' => true,
            'message' => "Jadwal bentrok dengan " . 
                        htmlspecialchars($bentrok['nama_guru']) . 
                        " pada hari " . $hari_list[$bentrok['hari']] . 
                        " jam ke-" . $bentrok['jam'] . 
                        " di kelas " . $bentrok['nama_kelas']
        ]);
    } else {
        echo json_encode(['bentrok' => false]);
    }
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
} 