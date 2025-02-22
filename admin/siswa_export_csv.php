<?php
// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="format_import_siswa.csv"');

// Create file pointer for output
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for proper Excel encoding
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Write header row
fputcsv($output, array('No', 'NIS', 'Nama Lengkap', 'Kelas', 'Jenis Kelamin', 'Alamat', 'No Tlp'));

// Write sample data
fputcsv($output, array('1', '1234567', 'Nama Siswa', 'X IPA 1', 'L', 'Alamat Siswa', '08123456789'));

// Close file pointer
fclose($output); 