<?php
include '../templates/header.php';
require_once '../config/database.php';
checkRole(['wakasek']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Validasi file ZIP
        if (empty($_FILES['foto_zip']['name'])) {
            throw new Exception('File ZIP tidak ditemukan!');
        }

        if ($_FILES['foto_zip']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error saat upload file: ' . $_FILES['foto_zip']['error']);
        }

        $zip = new ZipArchive;
        $zip_file = $_FILES['foto_zip']['tmp_name'];
        
        if ($zip->open($zip_file) === TRUE) {
            $upload_dir = '../assets/img/profile/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $success = 0;
            $skipped = 0;
            $errors = [];
            
            // Extract file zip
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                $fileinfo = pathinfo($filename);
                
                // Skip jika directory atau file tersembunyi
                if (empty($fileinfo['extension']) || $filename[0] === '.') {
                    continue;
                }
                
                // Cek ekstensi file
                $ext = strtolower($fileinfo['extension']);
                if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    // Ambil NIS dari nama file
                    $nis = pathinfo($fileinfo['filename'], PATHINFO_FILENAME);
                    
                    // Cek apakah siswa ada
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND role = 'siswa'");
                    $stmt->execute([$nis]);
                    $user = $stmt->fetch();
                    
                    if ($user) {
                        $new_filename = 'siswa_' . $nis . '_' . time() . '.' . $ext;
                        
                        // Extract dan copy file
                        if (copy("zip://".$zip_file."#".$filename, $upload_dir . $new_filename)) {
                            // Update database
                            $stmt = $pdo->prepare("UPDATE users SET foto = ? WHERE username = ? AND role = 'siswa'");
                            $stmt->execute([$new_filename, $nis]);
                            $success++;
                        } else {
                            $errors[] = "Gagal menyalin file untuk NIS: $nis";
                        }
                    } else {
                        $skipped++;
                        $errors[] = "NIS tidak ditemukan: $nis";
                    }
                } else {
                    $skipped++;
                    $errors[] = "File tidak didukung: $filename";
                }
            }
            
            $zip->close();
            
            if ($success > 0) {
                $pdo->commit();
                $_SESSION['success'] = "Berhasil mengupload $success foto.";
                if ($skipped > 0) {
                    $_SESSION['success'] .= " $skipped file dilewati.";
                }
                if (!empty($errors)) {
                    $_SESSION['error'] = "Beberapa error terjadi:\n" . implode("\n", $errors);
                }
            } else {
                throw new Exception('Tidak ada foto yang berhasil diupload. ' . implode("\n", $errors));
            }
        } else {
            throw new Exception('Gagal membuka file ZIP!');
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = $e->getMessage();
    }
}

header("Location: siswa.php");
exit();
?> 