<?php 
include '../templates/header.php';
include '../templates/sidebar.php';
require_once '../config/database.php';
checkRole(['guru', 'wali_kelas']);

// Set default date range (1 bulan terakhir)
$end_date = date('Y-m-d');
$start_date = date('Y-m-d', strtotime('-1 month'));

// Filter date range jika ada
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
}

// Ambil kelas yang diampu oleh wali kelas
$stmt = $pdo->prepare("SELECT k.id, k.nama_kelas 
                       FROM kelas k 
                       WHERE k.wali_kelas_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$kelas = $stmt->fetch();

if (!$kelas) {
    die("Anda belum ditugaskan sebagai wali kelas");
}

// Tambahkan query untuk mengambil daftar siswa di kelas ini
$stmt = $pdo->prepare("SELECT id, nama_lengkap 
                       FROM users 
                       WHERE role = 'siswa' 
                       AND kelas_id = ? 
                       ORDER BY nama_lengkap");
$stmt->execute([$kelas['id']]);
$siswa_list = $stmt->fetchAll();

// Query untuk mengambil data absensi kelas yang diampu
$query = "SELECT u.id, u.nis, u.nama_lengkap, k.nama_kelas, 
          a.tanggal,
          a.status
          FROM users u 
          JOIN kelas k ON u.kelas_id = k.id
          JOIN absensi a ON u.id = a.user_id 
          WHERE u.role = 'siswa'
          AND k.id = ?
          AND a.tanggal BETWEEN ? AND ?
          ORDER BY a.tanggal DESC, u.nama_lengkap";

$stmt = $pdo->prepare($query);
$stmt->execute([$kelas['id'], $start_date, $end_date]);
$absensi_list = $stmt->fetchAll();
?>

<!-- CSS untuk print -->
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
    
    /* Style untuk header print */
    .print-header {
        text-align: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #000;
    }
    
    .print-header h2 {
        margin: 0;
        padding: 0;
        font-size: 18pt;
        font-weight: bold;
    }
    
    .print-header p {
        margin: 5px 0;
        font-size: 11pt;
    }
    
    /* Atur style tabel */
    .table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 11pt;
    }
    
    .table th,
    .table td {
        border: 1px solid #000;
        padding: 8px;
        text-align: center;
    }
    
    .table th {
        background-color: #f4f4f4;
        font-weight: bold;
    }
    
    /* Format tanggal */
    .tanggal {
        white-space: nowrap;
    }
    
    /* Ganti badge dengan text biasa */
    .badge {
        padding: 0 !important;
        font-weight: normal !important;
        background: none !important;
        color: #000 !important;
    }
    
    /* Tampilkan header print */
    .d-print-block {
        display: block !important;
    }

    /* Tambahan style untuk layout print */
    @page {
        margin: 2cm;
    }

    body {
        font-family: "Times New Roman", Times, serif;
    }
}

/* Style untuk tampilan tabel di layar */
.table th, 
.table td {
    text-align: center;
    vertical-align: middle;
}
</style>

<!-- Header Print -->
<div class="d-none d-print-block print-header">
    <h2>LAPORAN ABSENSI SEKOLAH</h2>
    <p>Kelas: <?php echo htmlspecialchars($kelas['nama_kelas']); ?></p>
    <p>Periode: <?php echo date('d F Y', strtotime($start_date)); ?> s/d <?php echo date('d F Y', strtotime($end_date)); ?></p>
</div>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Laporan Absensi Sekolah</h1>
                    <h5 class="text-muted">Kelas: <?php echo htmlspecialchars($kelas['nama_kelas']); ?></h5>
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
                            <select class="form-control" id="searchInput" onchange="filterByName()">
                                <option value="">Pilih Siswa</option>
                                <?php foreach($siswa_list as $siswa): ?>
                                    <option value="<?php echo htmlspecialchars($siswa['nama_lengkap']); ?>">
                                        <?php echo htmlspecialchars($siswa['nama_lengkap']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="ml-auto">
                            <button type="button" class="btn btn-success mr-2" onclick="exportToExcel()">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                            <button type="button" class="btn btn-success mr-2" onclick="shareToWhatsApp()">
                                <i class="fab fa-whatsapp"></i> Share
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
    
    // Add kelas
    doc.setFontSize(12);
    doc.text('Kelas: <?php echo htmlspecialchars($kelas['nama_kelas']); ?>', 14, 25);
    
    // Add date range
    doc.setFontSize(11);
    doc.text(`Periode: ${document.querySelector('[name="start_date"]').value} s/d ${document.querySelector('[name="end_date"]').value}`, 14, 32);
    
    // Add table
    doc.autoTable({ 
        html: '#absensiTable',
        startY: 40,
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
    document.title = `Laporan Absensi Sekolah - Kelas <?php echo htmlspecialchars($kelas['nama_kelas']); ?> (${document.querySelector('[name="start_date"]').value} s/d ${document.querySelector('[name="end_date"]').value})`;
    
    // Print
    window.print();
    
    // Kembalikan judul asli
    document.title = originalTitle;
}

// Fungsi untuk filter berdasarkan nama
function filterByName() {
    var select = document.getElementById("searchInput");
    var filter = select.value;
    var table = document.getElementById("absensiTable");
    var tr = table.getElementsByTagName("tr");

    for (var i = 1; i < tr.length; i++) {
        var td = tr[i].getElementsByTagName("td")[3]; // Index 3 adalah kolom Nama Lengkap
        if (td) {
            var txtValue = td.textContent || td.innerText;
            if (filter === "" || txtValue === filter) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}

// Fungsi untuk share ke WhatsApp
function shareToWhatsApp() {
    // Ambil nama siswa dari select
    const select = document.getElementById("searchInput");
    const namaSiswa = select.value;
    
    // Siapkan pesan
    let message = `*LAPORAN ABSENSI SEKOLAH*\n`;
    message += `Periode: ${document.querySelector('[name="start_date"]').value} s/d ${document.querySelector('[name="end_date"]').value}\n`;
    message += `Kelas: <?php echo $kelas['nama_kelas']; ?>\n`;
    
    // Tambahkan nama siswa jika ada yang dipilih
    if (namaSiswa) {
        message += `Nama: ${namaSiswa}\n`;
    }
    message += `\n`;

    // Ambil data dari tabel
    const table = document.getElementById('absensiTable');
    const rows = table.getElementsByTagName('tr');
    let absenData = {
        'Hadir': 0,
        'Izin': 0,
        'Sakit': 0,
        'Alpha': 0
    };
    
    // Hitung total status
    let visibleRows = 0;
    for (let i = 1; i < rows.length; i++) {
        // Hanya hitung baris yang terlihat (jika ada filter nama)
        if (rows[i].style.display !== 'none') {
            visibleRows++;
            const status = rows[i].getElementsByTagName('td')[4].innerText.trim();
            absenData[status]++;
        }
    }
    
    // Tambahkan ringkasan ke pesan
    message += `*Ringkasan Absensi*\n`;
    message += `Hadir: ${absenData['Hadir']} Hari\n`;
    message += `Izin: ${absenData['Izin']} Hari\n`;
    message += `Sakit: ${absenData['Sakit']} Hari\n`;
    message += `Alpha: ${absenData['Alpha']} Hari`;
    
    // Encode pesan untuk URL
    const encodedMessage = encodeURIComponent(message);
    
    // Buka WhatsApp
    window.open(`https://wa.me/?text=${encodedMessage}`, '_blank');
}
</script>

<?php include '../templates/footer.php'; ?> 