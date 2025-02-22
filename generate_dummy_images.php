<?php
// Membuat direktori assets/img jika belum ada
$imgDir = 'assets/img';
if (!file_exists($imgDir)) {
    mkdir($imgDir, 0777, true);
}

// Membuat logo.png dengan ukuran lebih kecil
$logo = imagecreatetruecolor(64, 64);
$bg = imagecolorallocate($logo, 41, 128, 185); // Warna biru
$text_color = imagecolorallocate($logo, 255, 255, 255); // Warna putih
imagefill($logo, 0, 0, $bg);
imagestring($logo, 3, 12, 25, "LOGO", $text_color);
imagepng($logo, $imgDir . '/logo.jpg');
imagedestroy($logo);

// Membuat foto-awal.jpg dengan ukuran lebih kecil
$avatar = imagecreatetruecolor(100, 100); // Ukuran lebih kecil
$bg = imagecolorallocate($avatar, 52, 152, 219); // Warna biru muda
$text_color = imagecolorallocate($avatar, 255, 255, 255); // Warna putih

// Membuat lingkaran untuk avatar
$radius = 50;
$center_x = 50;
$center_y = 50;

// Isi background
imagefill($avatar, 0, 0, $bg);

// Tambahkan teks
imagestring($avatar, 2, 35, 45, "USER", $text_color);

imagejpeg($avatar, $imgDir . '/foto-awal.jpg', 90);
imagedestroy($avatar);

echo "Gambar dummy berhasil dibuat di folder $imgDir";
?> 