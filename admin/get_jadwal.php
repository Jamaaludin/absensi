<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    exit;
}

$guru_id = $_GET['guru_id'] ?? '';
$hari = $_GET['hari'] ?? '';

if (!$guru_id) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT jm.*, k.nama_kelas, u.nama_lengkap 
        FROM jam_mengajar jm 
        JOIN kelas k ON jm.kelas_id = k.id 
        JOIN users u ON jm.guru_id = u.id 
        WHERE jm.guru_id = ?";

$params = [$guru_id];

if ($hari && $hari !== 'Semua') {
    $sql .= " AND jm.hari = ?";
    $params[] = $hari;
}

$sql .= " ORDER BY FIELD(jm.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), jm.jam";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$jadwal = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($jadwal); 