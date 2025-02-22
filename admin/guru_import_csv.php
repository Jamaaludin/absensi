<?php
require_once '../config/database.php';
session_start();

if ($_FILES['file']['error'] == 0) {
    $fileName = $_FILES['file']['tmp_name'];
    
    try {
        $pdo->beginTransaction();
        
        // Array untuk konversi nama hari ke angka
        $hari_ke_angka = array(
            'senin' => 1,
            'selasa' => 2,
            'rabu' => 3,
            'kamis' => 4,
            'jumat' => 5,
            'sabtu' => 6,
            'minggu' => 7
        );
        
        if (($handle = fopen($fileName, "r")) !== FALSE) {
            // Skip header row
            fgetcsv($handle);
            
            $current_nip = null;
            $current_guru_id = null;
            $success_count = 0;
            
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $nip = $data[1];
                $nama = $data[2];
                $mata_pelajaran = $data[3];
                $jenis_kelamin = $data[4];
                $alamat = $data[5];
                $no_tlp = $data[6];
                $hari_text = strtolower(trim($data[7])); // Konversi ke lowercase dan hilangkan spasi
                $nama_kelas = $data[8];
                $jam = $data[9];

                // Validasi format hari
                if (!isset($hari_ke_angka[$hari_text])) {
                    throw new Exception("Format hari '$data[7]' tidak valid. Gunakan: Senin, Selasa, Rabu, Kamis, Jumat, Sabtu, Minggu");
                }
                
                // Konversi hari ke angka
                $hari = $hari_ke_angka[$hari_text];

                // Validasi data wajib
                if (empty($nip) || empty($nama) || empty($mata_pelajaran)) {
                    throw new Exception("NIP, Nama Lengkap, dan Mata Pelajaran wajib diisi");
                }
                
                // Jika NIP berbeda dengan sebelumnya, buat data guru baru
                if ($nip !== $current_nip) {
                    // Cek apakah NIP sudah ada
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE nip = ?");
                    $stmt->execute([$nip]);
                    $existing_guru = $stmt->fetch();
                    
                    if ($existing_guru) {
                        $current_guru_id = $existing_guru['id'];
                    } else {
                        // Cek apakah mata pelajaran sudah ada
                        $stmt = $pdo->prepare("SELECT id FROM mata_pelajaran WHERE nama = ?");
                        $stmt->execute([$mata_pelajaran]);
                        $mp = $stmt->fetch();
                        
                        if (!$mp) {
                            // Generate kode mata pelajaran
                            $kode = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $mata_pelajaran), 0, 3));
                            
                            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM mata_pelajaran WHERE kode LIKE ?");
                            $stmt->execute([$kode . '%']);
                            $count = $stmt->fetch()['total'];
                            
                            if ($count > 0) {
                                $kode = $kode . ($count + 1);
                            }
                            
                            // Insert mata pelajaran baru
                            $stmt = $pdo->prepare("INSERT INTO mata_pelajaran (kode, nama) VALUES (?, ?)");
                            $stmt->execute([$kode, $mata_pelajaran]);
                        }
                        
                        // Create user account with guru role
                        $username = $nip;
                        $password = password_hash($nip, PASSWORD_DEFAULT);
                        
                        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, nama_lengkap, nip, mata_pelajaran, jenis_kelamin, alamat, no_telp) 
                                             VALUES (?, ?, 'guru', ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$username, $password, $nama, $nip, $mata_pelajaran, $jenis_kelamin, $alamat, $no_tlp]);
                        
                        $current_guru_id = $pdo->lastInsertId();
                        $success_count++;
                    }
                    $current_nip = $nip;
                }
                
                // Cek keberadaan kelas
                $stmt = $pdo->prepare("SELECT id FROM kelas WHERE nama_kelas = ?");
                $stmt->execute([$nama_kelas]);
                $kelas = $stmt->fetch();
                
                if (!$kelas) {
                    throw new Exception("Nama Kelas '$nama_kelas' tidak ada di Manajemen Kelas");
                }
                
                // Cek duplikasi jadwal
                $stmt = $pdo->prepare("SELECT id FROM jam_mengajar WHERE guru_id = ? AND kelas_id = ? AND hari = ? AND jam = ?");
                $stmt->execute([$current_guru_id, $kelas['id'], $hari, $jam]);
                if (!$stmt->fetch()) {
                    // Insert jam mengajar jika belum ada
                    $stmt = $pdo->prepare("INSERT INTO jam_mengajar (guru_id, kelas_id, hari, jam) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$current_guru_id, $kelas['id'], $hari, $jam]);
                }
            }
            fclose($handle);
            
            $pdo->commit();
            $_SESSION['success_msg'] = "Berhasil mengimport data guru ($success_count guru) dan jadwal mengajar";
            header("Location: guru.php");
            exit();
        } else {
            throw new Exception("Gagal membuka file");
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_msg'] = "Error: " . $e->getMessage();
        header("Location: guru.php");
        exit();
    }
} else {
    $_SESSION['error_msg'] = "Error saat upload file";
    header("Location: guru.php");
    exit();
} 