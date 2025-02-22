CREATE TABLE absensi_pengasuhan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    tanggal DATE NOT NULL,
    status ENUM('hadir', 'hl', 'hn', 'bawabah') NOT NULL DEFAULT 'hadir',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_absensi (user_id, tanggal)
); 