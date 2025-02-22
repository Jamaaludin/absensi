<?php
session_start(); // Tambahkan ini di awal file
require_once '../config/database.php';

// Cek apakah user sudah login dan memiliki role wakasek
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'wakasek') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Set header JSON
header('Content-Type: application/json');

// Ambil daftar mata pelajaran untuk select options
$query = "SELECT * FROM mata_pelajaran ORDER BY nama";
$stmt = $pdo->query($query);
$mapel_list = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Debug log
        error_log('Received POST data: ' . print_r($_POST, true));
        
        // Validasi input
        if (!isset($_POST['id']) || empty($_POST['id'])) {
            throw new Exception('ID guru tidak valid');
        }
        
        $pdo->beginTransaction();
        
        $id = $_POST['id'];
        $username = $_POST['username'];
        $nama_lengkap = $_POST['nama_lengkap'];
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $no_telp = $_POST['no_telp'];
        
        // Validasi mata pelajaran
        if (!isset($_POST['mata_pelajaran']) || !is_array($_POST['mata_pelajaran'])) {
            error_log('Mata pelajaran error: ' . print_r($_POST['mata_pelajaran'], true));
            throw new Exception("Format mata pelajaran tidak valid");
        }
        
        // Filter empty values dan duplikat
        $mata_pelajaran = array_filter($_POST['mata_pelajaran'], function($value) {
            return !empty(trim($value));
        });
        $mata_pelajaran = array_unique($mata_pelajaran);
        
        if (empty($mata_pelajaran)) {
            throw new Exception("Minimal satu mata pelajaran harus diisi");
        }
        
        // Debug log
        error_log('Mata pelajaran yang akan disimpan: ' . print_r($mata_pelajaran, true));
        
        // Gabungkan array jadi string
        $mata_pelajaran_str = implode(', ', $mata_pelajaran);
        
        // Update data guru
        $stmt = $pdo->prepare("UPDATE users SET 
            username = ?, 
            nama_lengkap = ?, 
            jenis_kelamin = ?, 
            no_telp = ?, 
            mata_pelajaran = ? 
            WHERE id = ?");
            
        $result = $stmt->execute([
            $username, 
            $nama_lengkap, 
            $jenis_kelamin, 
            $no_telp, 
            $mata_pelajaran_str, 
            $id
        ]);
        
        if (!$result) {
            throw new Exception("Gagal mengupdate data: " . implode(", ", $stmt->errorInfo()));
        }
        
        $pdo->commit();
        
        $response = [
            'success' => true,
            'message' => 'Data guru berhasil diupdate!'
        ];
        
        error_log('Sending response: ' . json_encode($response));
        echo json_encode($response);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('Error in guru_edit.php: ' . $e->getMessage());
        
        $response = [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
        
        error_log('Sending error response: ' . json_encode($response));
        echo json_encode($response);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}

exit; 