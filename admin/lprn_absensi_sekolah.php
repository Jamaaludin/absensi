<?php 
include '../templates/header.php';
include '../templates/sidebar.php';
require_once '../config/database.php';
checkRole(['admin', 'guru', 'pengasuhan']);

// Set default date range (1 bulan terakhir)
$end_date = date('Y-m-d');
$start_date = date('Y-m-d', strtotime('-1 month'));

// Filter date range jika ada
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
}

// Tambahkan query untuk mengambil daftar kelas setelah koneksi database
$stmt = $pdo->query("SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas");
$kelas_list = $stmt->fetchAll();

// Modifikasi query untuk filter berdasarkan kelas
$where_clause = "";
$params = [$start_date, $end_date];

if (isset($_GET['kelas_id']) && $_GET['kelas_id'] != '') {
    $where_clause = "AND k.id = ?";
    $params[] = $_GET['kelas_id'];
}

// Update query absensi untuk menambahkan filter kelas
$query = "SELECT u.id, u.nis, u.nama_lengkap, k.nama_kelas, 
          a.tanggal,
          a.status
          FROM users u 
          JOIN kelas k ON u.kelas_id = k.id
          JOIN absensi a ON u.id = a.user_id 
          WHERE u.role = 'siswa'
          AND a.tanggal BETWEEN ? AND ?
          $where_clause
          ORDER BY a.tanggal DESC, k.nama_kelas, u.nama_lengkap";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$absensi_list = $stmt->fetchAll();
?>

<!-- Tambahkan CSS khusus untuk print di bagian atas file, setelah include header -->
<style>
@media print {
    /* Sembunyikan elemen yang tidak perlu di print */
    .main-header,
    .main-sidebar,
    .card-header,
    .breadcrumb,
    .btn,
    footer {
        display: none !important;
    }
    
    /* Reset margin dan padding */
    .content-wrapper {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    
    /* Atur style tabel */
    .table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table th,
    .table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    
    /* Ganti badge dengan text biasa */
    .badge {
        padding: 0 !important;
        font-weight: normal !important;
        background: none !important;
        color: #000 !important;
    }
    
    /* Tambah header print */
    .print-header {
        text-align: center;
        margin-bottom: 20px;
    }
    
    .print-header h2 {
        margin: 0;
        padding: 0;
    }
    
    .print-header p {
        margin: 5px 0;
    }
    
    /* Tampilkan header print */
    .d-print-block {
        display: block !important;
    }
}
</style>

<!-- Tambahkan div untuk header print sebelum card -->
<div class="d-none d-print-block print-header">
    <h2>LAPORAN ABSENSI SEKOLAH</h2>
    <p>Periode: <?php echo date('d/m/Y', strtotime($start_date)); ?> s/d <?php echo date('d/m/Y', strtotime($end_date)); ?></p>
</div>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Laporan Absensi Sekolah</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Laporan Absensi Sekolah</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <form action="" method="GET" class="form-inline" id="filterForm">
                        <div class="form-group mr-2">
                            <label class="mr-2">Periode:</label>
                            <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>" 
                                   onchange="this.form.submit()" required>
                            <span class="mx-2">s/d</span>
                            <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>" 
                                   onchange="this.form.submit()" required>
                        </div>
                        <div class="form-group mr-2">
                            <select class="form-control" name="kelas_id" onchange="this.form.submit()">
                                <option value="">Semua Kelas</option>
                                <?php foreach($kelas_list as $k): ?>
                                    <option value="<?php echo $k['id']; ?>" <?php echo (isset($_GET['kelas_id']) && $_GET['kelas_id'] == $k['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($k['nama_kelas']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="ml-auto">
                            <button type="button" class="btn btn-success mr-2" onclick="exportToExcel()">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                            <button type="button" class="btn btn-danger mr-2" onclick="exportToPDF()">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </button>
                            <button type="button" class="btn btn-info" onclick="printReport()">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap" id="absensiTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>NIS</th>
                                <th>Nama Lengkap</th>
                                <th>Kelas</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($absensi_list as $index => $a): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($a['tanggal'])); ?></td>
                                <td><?php echo htmlspecialchars($a['nis']); ?></td>
                                <td><?php echo htmlspecialchars($a['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars($a['nama_kelas']); ?></td>
                                <td>
                                    <?php
                                    $badge_class = 'success';
                                    $status_text = ucfirst($a['status']);
                                    switch($a['status']) {
                                        case 'izin':
                                            $badge_class = 'secondary';
                                            break;
                                        case 'sakit':
                                            $badge_class = 'warning';
                                            break;
                                        case 'alpha':
                                            $badge_class = 'danger';
                                            break;
                                    }
                                    ?>
                                    <span class="badge badge-<?php echo $badge_class; ?> d-print-none">
                                        <?php echo $status_text; ?>
                                    </span>
                                    <span class="d-none d-print-inline">
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Script untuk export dan print -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.20/jspdf.plugin.autotable.min.js"></script>

<script>
// Fungsi untuk export ke Excel
function exportToExcel() {
    const table = document.getElementById('absensiTable');
    const wb = XLSX.utils.table_to_book(table, {sheet: "Absensi"});
    const filename = `Laporan_Absensi_${document.querySelector('[name="start_date"]').value}_${document.querySelector('[name="end_date"]').value}.xlsx`;
    XLSX.writeFile(wb, filename);
}

// Fungsi untuk export ke PDF
function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    // Add title
    doc.setFontSize(16);
    doc.text('Laporan Absensi Sekolah', 14, 15);
    
    // Add date range
    doc.setFontSize(11);
    doc.text(`Periode: ${document.querySelector('[name="start_date"]').value} s/d ${document.querySelector('[name="end_date"]').value}`, 14, 25);
    
    // Add table
    doc.autoTable({ 
        html: '#absensiTable',
        startY: 30,
        styles: { fontSize: 8 },
        columnStyles: { 0: { cellWidth: 10 } }
    });
    
    // Save PDF
    doc.save(`Laporan_Absensi_${document.querySelector('[name="start_date"]').value}_${document.querySelector('[name="end_date"]').value}.pdf`);
}

// Fungsi untuk print
function printReport() {
    // Simpan judul halaman asli
    const originalTitle = document.title;
    
    // Ganti judul untuk print
    document.title = `Laporan Absensi Sekolah (${document.querySelector('[name="start_date"]').value} s/d ${document.querySelector('[name="end_date"]').value})`;
    
    // Print
    window.print();
    
    // Kembalikan judul asli
    document.title = originalTitle;
}
</script>

<?php include '../templates/footer.php'; ?> 