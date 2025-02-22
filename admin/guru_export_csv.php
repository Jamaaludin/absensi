<?php
require_once '../config/database.php';
require_once '../config/init.php';
checkRole(['admin']);

// Set header untuk download file CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="format_import_guru.csv"');

// Buka output stream
$output = fopen('php://output', 'w');

// Tulis header CSV
fputcsv($output, [
    'NIP',
    'Username',
    'Nama Lengkap',
    'Jenis Kelamin',
    'No. Telp',
    'Mata Pelajaran',
    'Hari',
    'Kelas',
    'Jam Pelajaran'
]);

// Tulis contoh data
$contoh_data = [
    [
        '123456',                    // NIP
        'guru1',                     // Username
        'Budi Santoso',             // Nama Lengkap
        'L',                        // Jenis Kelamin (L/P)
        '081234567890',             // No. Telp
        'Matematika,Fisika',        // Multiple Mata Pelajaran
        'Senin,Selasa,Rabu',         // Multiple Hari (Senin=1, Selasa=2, dst)
        'X IPA 1,X IPA 2,X IPA 1',  // Multiple Kelas
        '1-2,3-4,1-2'               // Multiple Jam Pelajaran
    ],
    [
        '789012',                    // NIP
        'guru2',                     // Username
        'Siti Aminah',              // Nama Lengkap
        'P',                        // Jenis Kelamin (L/P)
        '085678901234',             // No. Telp
        'Biologi,Kimia',            // Multiple Mata Pelajaran
        'Rabu,Kamis',               // Multiple Hari
        'XI IPA 1,XI IPA 2',        // Multiple Kelas
        '3-4,5-6'                   // Multiple Jam Pelajaran
    ]
];

// Tulis contoh data ke CSV
foreach ($contoh_data as $row) {
    fputcsv($output, $row);
}

// Tutup file
fclose($output);
exit; 