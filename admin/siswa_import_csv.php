<?php
require_once '../config/database.php';

if ($_FILES['file']['error'] == 0) {
    $fileName = $_FILES['file']['tmp_name'];
    
    try {
        $pdo->beginTransaction();
        
        if (($handle = fopen($fileName, "r")) !== FALSE) {
            // Skip header row
            fgetcsv($handle);
            
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $nis = $data[1];
                $nama = $data[2];
                $kelas = $data[3];
                $jenis_kelamin = $data[4];
                $alamat = $data[5];
                $no_tlp = $data[6];
                
                // Check if kelas exists, if not insert it
                $stmt = $pdo->prepare("SELECT id FROM kelas WHERE nama_kelas = ?");
                $stmt->execute([$kelas]);
                $result = $stmt->fetch();
                
                if (!$result) {
                    $stmt = $pdo->prepare("INSERT INTO kelas (nama_kelas) VALUES (?)");
                    $stmt->execute([$kelas]);
                    $kelas_id = $pdo->lastInsertId();
                } else {
                    $kelas_id = $result['id'];
                }
                
                // Create user account with siswa role
                $username = $nis;
                $password = password_hash($nis, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role, nama_lengkap, kelas_id, nis, jenis_kelamin, alamat, no_telp) 
                                     VALUES (?, ?, 'siswa', ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$username, $password, $nama, $kelas_id, $nis, $jenis_kelamin, $alamat, $no_tlp]);
            }
            fclose($handle);
            
            $pdo->commit();
            header("Location: siswa.php?pesan=Import data berhasil");
            exit();
        } else {
            throw new Exception("Gagal membuka file");
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: siswa.php?pesan=Error: " . $e->getMessage());
        exit();
    }
} else {
    header("Location: siswa.php?pesan=Error upload file");
    exit();
} 