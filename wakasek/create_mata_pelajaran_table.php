<?php
require_once '../config/database.php';

try {
    // Drop table if exists untuk memastikan struktur yang benar
    $pdo->exec("DROP TABLE IF EXISTS mata_pelajaran");
    
    // Buat tabel baru
    $sql = "CREATE TABLE mata_pelajaran (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nama_mapel VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    
    // Insert beberapa data awal
    $pdo->exec("INSERT INTO mata_pelajaran (nama_mapel) VALUES 
        ('Matematika'),
        ('Bahasa Indonesia'),
        ('Bahasa Inggris'),
        ('Fisika'),
        ('Kimia'),
        ('Biologi')
    ");
    
    echo "Tabel mata_pelajaran berhasil dibuat dan data awal telah ditambahkan!";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 