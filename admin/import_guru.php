<?php
require_once '../config/database.php';
require_once '../config/init.php';
checkRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['csv_file'])) {
    try {
        $pdo->beginTransaction();
        
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, "r");
        
        // Skip header jika ada
        $header = fgetcsv($handle);
        
        while (($data = fgetcsv($handle)) !== FALSE) {
            $nip = $data[0];
            $username = $data[1];
            $nama_lengkap = $data[2];
            $jenis_kelamin = $data[3];
            $no_telp = $data[4];
            $mata_pelajaran = $data[5]; // String mata pelajaran dipisahkan koma
            $hari = $data[6];
            $kelas = $data[7]; // String kelas dipisahkan koma
            $jam_pel = $data[8]; // String jam pelajaran dipisahkan koma
            
            // Cek apakah guru sudah ada
            $stmt = $pdo->prepare("SELECT id FROM users WHERE nip = ? OR username = ?");
            $stmt->execute([$nip, $username]);
            $existing_guru = $stmt->fetch();
            
            if ($existing_guru) {
                // Update guru yang ada
                $guru_id = $existing_guru['id'];
                $stmt = $pdo->prepare("UPDATE users SET 
                    nama_lengkap = ?,
                    jenis_kelamin = ?,
                    no_telp = ?,
                    mata_pelajaran = ?
                    WHERE id = ?");
                $stmt->execute([
                    $nama_lengkap,
                    $jenis_kelamin,
                    $no_telp,
                    $mata_pelajaran,
                    $guru_id
                ]);
            } else {
                // Insert guru baru
                $password = password_hash($username, PASSWORD_DEFAULT); // Default password sama dengan username
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role, nip, nama_lengkap, 
                    jenis_kelamin, no_telp, mata_pelajaran) 
                    VALUES (?, ?, 'guru', ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $username,
                    $password,
                    $nip,
                    $nama_lengkap,
                    $jenis_kelamin,
                    $no_telp,
                    $mata_pelajaran
                ]);
                $guru_id = $pdo->lastInsertId();
            }
            
            // Proses jadwal mengajar
            $hari_array = explode(',', $hari);
            $kelas_array = explode(',', $kelas);
            $jam_array = explode(',', $jam_pel);
            $mapel_array = explode(',', $mata_pelajaran);
            $mapel_array = array_map('trim', $mapel_array);
            
            // Mapping nama hari ke angka
            $hari_mapping = [
                'Senin' => 1,
                'Selasa' => 2,
                'Rabu' => 3,
                'Kamis' => 4,
                'Jumat' => 5,
                'Sabtu' => 6,
                'Minggu' => 7
            ];
            
            // Pastikan jumlah data sama
            if (count($hari_array) === count($kelas_array) && count($kelas_array) === count($jam_array)) {
                for ($i = 0; $i < count($hari_array); $i++) {
                    $nama_hari = ucfirst(strtolower(trim($hari_array[$i])));
                    $hari_ini = isset($hari_mapping[$nama_hari]) ? $hari_mapping[$nama_hari] : 0;
                    $kelas_ini = trim($kelas_array[$i]);
                    $jam_range = trim($jam_array[$i]);
                    $mapel_ini = trim($mapel_array[$i % count($mapel_array)]);
                    
                    // Proses range jam pelajaran (contoh: "1-2" menjadi [1,2])
                    $jam_parts = explode('-', $jam_range);
                    $jam_mulai = (int)$jam_parts[0];
                    $jam_akhir = isset($jam_parts[1]) ? (int)$jam_parts[1] : $jam_mulai;
                    
                    // Ambil ID kelas
                    $stmt = $pdo->prepare("SELECT id FROM kelas WHERE nama_kelas = ?");
                    $stmt->execute([$kelas_ini]);
                    $kelas_data = $stmt->fetch();
                    
                    // Cek dan tambahkan mata pelajaran ke tabel mata_pelajaran jika belum ada
                    $stmt = $pdo->prepare("INSERT IGNORE INTO mata_pelajaran (nama) VALUES (?)");
                    $stmt->execute([$mapel_ini]);
                    
                    if ($kelas_data) {
                        // Insert jadwal untuk setiap jam dalam range
                        for ($jam = $jam_mulai; $jam <= $jam_akhir; $jam++) {
                            // Insert atau update jadwal
                            $stmt = $pdo->prepare("INSERT INTO jam_mengajar 
                                (guru_id, kelas_id, hari, jam, mata_pelajaran) 
                                VALUES (?, ?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE jam = ?, mata_pelajaran = ?");
                            $stmt->execute([
                                $guru_id,
                                $kelas_data['id'],
                                $hari_ini,
                                $jam,
                                $mapel_ini,
                                $jam,
                                $mapel_ini
                            ]);
                        }
                    }
                }
            }
        }
        
        fclose($handle);
        $pdo->commit();
        
        $_SESSION['success_msg'] = "Data guru berhasil diimport!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_msg'] = "Error: " . $e->getMessage();
    }
}

header("Location: guru.php");
exit; 