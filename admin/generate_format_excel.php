<?php
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set header
$headers = ['NIS', 'Nama Lengkap', 'Jenis Kelamin (L/P)', 'Kelas', 'Alamat', 'No Telp'];
foreach ($headers as $idx => $header) {
    $sheet->setCellValueByColumnAndRow($idx + 1, 1, $header);
}

// Contoh data
$example = [
    ['202401001', 'Ahmad Siswa', 'L', 'X-A', 'Jl. Contoh No. 1', '081234567890'],
    ['202401002', 'Budi Siswa', 'L', 'X-A', 'Jl. Contoh No. 2', '081234567891'],
];

foreach ($example as $row => $data) {
    foreach ($data as $col => $value) {
        $sheet->setCellValueByColumnAndRow($col + 1, $row + 2, $value);
    }
}

// Set style
$sheet->getStyle('A1:F1')->getFont()->setBold(true);
foreach (range('A', 'F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Set header untuk download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="format_import_siswa.xlsx"');
header('Cache-Control: max-age=0');

// Create file dan output langsung ke browser
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit; 