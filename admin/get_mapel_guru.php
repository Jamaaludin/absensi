<?php
require_once '../config/database.php';
require_once '../config/init.php';

if (!isset($_GET['guru_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID guru tidak ditemukan']);
    exit;
}

$guru_id = $_GET['guru_id'];

try {
    $stmt = $pdo->prepare("SELECT mata_pelajaran FROM guru WHERE id = ?");
    $stmt->execute([$guru_id]);
    $guru = $stmt->fetch();
    
    // Konversi string mata pelajaran menjadi array
    $mapel = $guru['mata_pelajaran'] ? explode(',', $guru['mata_pelajaran']) : [];
    
    header('Content-Type: application/json');
    echo json_encode($mapel);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 