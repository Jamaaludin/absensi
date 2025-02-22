-- Hapus tabel jika sudah ada (karena ada foreign key)
DROP TABLE IF EXISTS absensi;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS kelas;
DROP TABLE IF EXISTS jam_mengajar;
DROP TABLE IF EXISTS mata_pelajaran;

-- Buat tabel kelas
CREATE TABLE kelas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_kelas VARCHAR(20) NOT NULL,
    wali_kelas_id INT,
    tahun_ajaran VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Buat tabel users dengan kolom tambahan untuk guru
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'wakasek', 'guru', 'pengasuhan', 'siswa', 'wali_kelas') NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    kelas_id INT NULL,
    nis VARCHAR(20) NULL,
    nip VARCHAR(20) NULL,
    mata_pelajaran VARCHAR(50) NULL,
    jenis_kelamin ENUM('L','P') NULL,
    alamat TEXT NULL,
    no_telp VARCHAR(15) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE SET NULL,
    is_wali_kelas TINYINT(1) DEFAULT 0
);

-- Buat tabel absensi
CREATE TABLE absensi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    tanggal DATE NOT NULL,
    status ENUM('hadir', 'izin', 'sakit', 'alpha') NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_absensi (user_id, tanggal)
);

-- Buat tabel jam_mengajar
CREATE TABLE jam_mengajar (
    id INT PRIMARY KEY AUTO_INCREMENT,
    guru_id INT NOT NULL,
    kelas_id INT NOT NULL,
    hari ENUM('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu') NOT NULL,
    jam INT NOT NULL CHECK (jam BETWEEN 1 AND 8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guru_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE,
    UNIQUE KEY unique_jadwal (kelas_id, hari, jam)
);

-- Buat tabel mata_pelajaran
CREATE TABLE mata_pelajaran (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode VARCHAR(10) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert data kelas
INSERT INTO kelas (nama_kelas, tahun_ajaran) VALUES 
('X-A', '2024/2025'),
('X-B', '2024/2025'),
('XI-A', '2024/2025'),
('XI-B', '2024/2025'),
('XII-A', '2024/2025'),
('XII-B', '2024/2025');

-- Insert dummy users
INSERT INTO users (username, password, role, nama_lengkap) VALUES
-- Admin
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administrator Sistem'),

-- Wakasek Kesiswaan
('wakasek1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'wakasek', 'Dr. Ahmad Supriyadi, M.Pd'),

-- Guru Mata Pelajaran
('guru1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'guru', 'Budi Santoso, S.Pd'),
('guru2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'guru', 'Siti Aminah, M.Pd'),
('guru3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'guru', 'Rudi Hermawan, S.Pd'),

-- Pengasuhan
('pengasuh1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pengasuhan', 'Ustadz Abdul Rahman'),
('pengasuh2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pengasuhan', 'Ustadzah Fatimah');

-- Insert dummy siswa
INSERT INTO users (username, password, role, nama_lengkap, kelas_id, nis, jenis_kelamin, alamat, no_telp) 
SELECT 
    CONCAT('siswa', k.id, n),
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'siswa',
    CASE 
        WHEN n = 1 THEN CONCAT('Ahmad Siswa ', k.nama_kelas)
        WHEN n = 2 THEN CONCAT('Budi Siswa ', k.nama_kelas)
        WHEN n = 3 THEN CONCAT('Cindy Siswa ', k.nama_kelas)
        WHEN n = 4 THEN CONCAT('Deni Siswa ', k.nama_kelas)
        ELSE CONCAT('Eko Siswa ', k.nama_kelas)
    END,
    k.id,
    CONCAT('2024', LPAD(k.id, 2, '0'), LPAD(n, 3, '0')),
    CASE WHEN n % 2 = 0 THEN 'L' ELSE 'P' END,
    CONCAT('Alamat Siswa ', n, ' Kelas ', k.nama_kelas),
    CONCAT('08', FLOOR(RAND() * 1000000000))
FROM kelas k
CROSS JOIN (SELECT 1 AS n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) numbers;

-- Insert dummy absensi untuk hari ini
INSERT INTO absensi (user_id, tanggal, status, keterangan)
SELECT 
    id,
    CURRENT_DATE,
    CASE 
        WHEN RAND() < 0.8 THEN 'hadir'
        WHEN RAND() < 0.9 THEN 'sakit'
        WHEN RAND() < 0.95 THEN 'izin'
        ELSE 'alpha'
    END,
    'Keterangan absensi'
FROM users 
WHERE role = 'siswa';

-- Update enum role di tabel users
ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'wakasek', 'guru', 'pengasuhan', 'siswa', 'wali_kelas') NOT NULL; 