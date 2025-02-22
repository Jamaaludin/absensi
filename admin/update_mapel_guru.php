<?php
require_once '../config/database.php';
require_once '../config/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_POST['guru_id']) || !isset($_POST['mapel'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Data tidak lengkap']);
    exit;
}

try {
    $guru_id = $_POST['guru_id'];
    $mapel = array_filter($_POST['mapel']); // Hapus empty values
    $mapel_string = implode(',', $mapel);
    
    $stmt = $pdo->prepare("UPDATE guru SET mata_pelajaran = ? WHERE id = ?");
    $stmt->execute([$mapel_string, $guru_id]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 