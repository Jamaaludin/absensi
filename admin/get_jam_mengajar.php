<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    exit;
}

$guru_id = $_GET['guru_id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT jm.*, k.nama_kelas 
    FROM jam_mengajar jm 
    JOIN kelas k ON jm.kelas_id = k.id 
    WHERE jm.guru_id = ? 
    ORDER BY FIELD(jm.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), jm.jam
");
$stmt->execute([$guru_id]);
echo json_encode($stmt->fetchAll()); 