<?php
require_once '../config/database.php';

try {
    // Drop table if exists
    $pdo->exec("DROP TABLE IF EXISTS jam_mengajar");
    
    // Create new table
    $sql = "CREATE TABLE jam_mengajar (
        id INT PRIMARY KEY AUTO_INCREMENT,
        guru_id INT NOT NULL,
        kelas_id INT NOT NULL,
        hari INT NOT NULL,
        jam INT NOT NULL,
        mata_pelajaran VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (guru_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql);
    echo "Tabel jam_mengajar berhasil dibuat ulang!";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
} 