<?php
require_once '../config/database.php';

try {
    // Tambah kolom mata_pelajaran ke tabel jam_mengajar
    $sql = "ALTER TABLE jam_mengajar 
            ADD COLUMN mata_pelajaran VARCHAR(100) NOT NULL 
            AFTER jam";
    
    $pdo->exec($sql);
    echo "Berhasil menambahkan kolom mata_pelajaran ke tabel jam_mengajar!";
    
} catch(PDOException $e) {
    if ($e->getCode() == '42S21') { // Duplicate column error
        echo "Kolom mata_pelajaran sudah ada di tabel jam_mengajar";
    } else {
        echo "Error: " . $e->getMessage();
    }
} 