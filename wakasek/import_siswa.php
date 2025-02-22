<?php
include '../templates/header.php';
require_once '../config/database.php';
checkRole(['wakasek']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        if (empty($_FILES['csv_file']['name'])) {
            throw new Exception('File CSV tidak ditemukan!');
        }

        $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
        // Skip header
        fgetcsv($file);
        
        $success = 0;
        $errors = [];
        
        while (($row = fgetcsv($file)) !== FALSE) {
            try {
                list($nis, $nama, $jk, $kelas_id) = $row;
                
                // Validasi data
                if (empty($nis) || empty($nama) || empty($jk) || empty($kelas_id)) {
                    throw new Exception("Data tidak lengkap untuk NIS: $nis");
                }

                // Cek NIS unik
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
                $stmt->execute([$nis]);
                if ($stmt->fetchColumn() > 0) {
                    throw new Exception("NIS $nis sudah terdaftar");
                }

                // Insert data
                $password = password_hash($nis, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, jenis_kelamin, kelas_id, role) VALUES (?, ?, ?, ?, ?, 'siswa')");
                $stmt->execute([$nis, $password, $nama, $jk, $kelas_id]);
                
                $success++;
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }
        
        fclose($file);
        
        if ($success > 0) {
            $pdo->commit();
            $_SESSION['success'] = "$success data siswa berhasil diimport.";
            if (!empty($errors)) {
                $_SESSION['error'] = "Beberapa data gagal diimport:\n" . implode("\n", $errors);
            }
        } else {
            throw new Exception("Tidak ada data yang berhasil diimport:\n" . implode("\n", $errors));
        }
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = $e->getMessage();
    }
}

header("Location: siswa.php");
exit();
?> 