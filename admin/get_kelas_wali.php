<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    exit;
}

$guru_id = $_GET['guru_id'] ?? '';

if ($guru_id) {
    $stmt = $pdo->prepare("SELECT id as kelas_id FROM kelas WHERE wali_kelas_id = ?");
    $stmt->execute([$guru_id]);
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC) ?: ['kelas_id' => null]);
} else {
    echo json_encode(['kelas_id' => null]);
} 