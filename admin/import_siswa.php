<?php
require '../vendor/autoload.php';
require_once '../config/database.php';
session_start();

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        if (!isset($_FILES['file']['tmp_name'])) {
            throw new Exception('Pilih file terlebih dahulu!');
        }

        $spreadsheet = IOFactory::load($_FILES['file']['tmp_name']);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        // Skip header row
        array_shift($rows);
        
        $pdo->beginTransaction();
        
        foreach ($rows as $row) {
            if (empty($row[0])) continue; // Skip empty rows
            
            // Generate username dan password dari NIS
            $username = 'siswa' . $row[0]; // NIS sebagai username
            $password = password_hash($row[0], PASSWORD_DEFAULT); // NIS sebagai password default
            
            // Cek kelas
            $stmt = $pdo->prepare("SELECT id FROM kelas WHERE nama_kelas = ?");
            $stmt->execute([$row[3]]);
            $kelas = $stmt->fetch();
            
            if (!$kelas) {
                throw new Exception("Kelas '{$row[3]}' tidak ditemukan!");
            }
            
            // Insert data siswa
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, nis, nama_lengkap, 
                                  kelas_id, jenis_kelamin, alamat, no_telp) 
                                  VALUES (?, ?, 'siswa', ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $username,
                $password,
                $row[0], // NIS
                $row[1], // Nama Lengkap
                $kelas['id'],
                $row[2], // Jenis Kelamin
                $row[4], // Alamat
                $row[5]  // No Telp
            ]);
        }
        
        $pdo->commit();
        $_SESSION['success'] = "Data siswa berhasil diimport!";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

header('Location: siswa.php');
exit(); 