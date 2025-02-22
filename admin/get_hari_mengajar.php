<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    exit;
}

$guru_id = $_GET['guru_id'] ?? '';

if ($guru_id) {
    // Ambil hari-hari dimana guru mengajar
    $stmt = $pdo->prepare("
        SELECT DISTINCT hari 
        FROM jam_mengajar 
        WHERE guru_id = ? 
        ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu')
    ");
    $stmt->execute([$guru_id]);
    $hari_mengajar = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($hari_mengajar)) {
        echo json_encode([
            'success' => false,
            'message' => 'Guru belum memiliki jadwal mengajar'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'data' => $hari_mengajar
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ID guru tidak valid'
    ]);
} 