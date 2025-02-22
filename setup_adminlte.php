<?php
// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fungsi untuk menampilkan pesan
function showMessage($message, $isError = false) {
    echo ($isError ? "❌ " : "✅ ") . $message . "\n";
}

try {
    // Buat folder utama jika belum ada
    $mainFolders = ['dist', 'plugins'];
    foreach ($mainFolders as $folder) {
        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
            showMessage("Folder $folder berhasil dibuat");
        }
    }

    // Download file yang diperlukan
    $files = [
        // AdminLTE CSS & JS
        'https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css' => 'dist/css/adminlte.min.css',
        'https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js' => 'dist/js/adminlte.min.js',
        
        // jQuery
        'https://code.jquery.com/jquery-3.6.0.min.js' => 'plugins/jquery/jquery.min.js',
        
        // Bootstrap
        'https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js' => 'plugins/bootstrap/js/bootstrap.bundle.min.js',
        
        // Font Awesome CSS
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css' => 'plugins/fontawesome-free/css/all.min.css',
        
        // Select2
        'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css' => 'plugins/select2/css/select2.min.css',
        'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js' => 'plugins/select2/js/select2.full.min.js',
        'https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.min.css' => 'plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css'
    ];

    // Pastikan semua folder yang diperlukan ada
    foreach ($files as $url => $path) {
        $dir = dirname($path);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
            showMessage("Folder $dir berhasil dibuat");
        }
    }

    // Download semua file
    foreach ($files as $url => $path) {
        if (!file_exists($path)) {
            $content = @file_get_contents($url);
            if ($content === false) {
                throw new Exception("Gagal mengunduh file dari: $url");
            }
            if (file_put_contents($path, $content) === false) {
                throw new Exception("Gagal menyimpan file ke: $path");
            }
            showMessage("File $path berhasil diunduh");
        } else {
            showMessage("File $path sudah ada");
        }
    }

    // Download Font Awesome webfonts
    $webfontsFolder = 'plugins/fontawesome-free/webfonts';
    if (!file_exists($webfontsFolder)) {
        mkdir($webfontsFolder, 0777, true);
        showMessage("Folder webfonts berhasil dibuat");
    }

    $fontAwesomeFonts = [
        'fa-solid-900.woff2',
        'fa-regular-400.woff2',
        'fa-brands-400.woff2'
    ];

    foreach ($fontAwesomeFonts as $font) {
        $fontUrl = "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/webfonts/$font";
        $fontPath = "$webfontsFolder/$font";
        
        if (!file_exists($fontPath)) {
            $content = @file_get_contents($fontUrl);
            if ($content === false) {
                throw new Exception("Gagal mengunduh font: $font");
            }
            if (file_put_contents($fontPath, $content) === false) {
                throw new Exception("Gagal menyimpan font: $font");
            }
            showMessage("Font $font berhasil diunduh");
        } else {
            showMessage("Font $font sudah ada");
        }
    }

    showMessage("\nSetup AdminLTE berhasil selesai! 🎉");
    showMessage("Silakan refresh halaman Anda untuk melihat perubahan.");

} catch (Exception $e) {
    showMessage($e->getMessage(), true);
    showMessage("Setup gagal, silakan coba lagi.", true);
}
?> 