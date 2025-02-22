<?php 
include '../templates/header.php';
include '../templates/sidebar.php';
require_once '../config/database.php';
checkRole(['wakasek']);

// Set default date range (1 bulan terakhir)
$end_date = date('Y-m-d');
$start_date = date('Y-m-d', strtotime('-1 month'));

// Filter date range jika ada
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
}

// Ambil daftar kelas
$stmt = $pdo->query("SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas");
$kelas_list = $stmt->fetchAll();

// Filter kelas
$kelas_id = isset($_GET['kelas_id']) ? $_GET['kelas_id'] : '';

// Query untuk mengambil data absensi
$query = "SELECT a.*, u.nama_lengkap, u.username as nis, k.nama_kelas, 
          DATE_FORMAT(a.tanggal, '%d/%m/%Y') as tgl
          FROM absensi a
          JOIN users u ON a.user_id = u.id
          JOIN kelas k ON u.kelas_id = k.id
          WHERE a.tanggal BETWEEN ? AND ?";

$params = [$start_date, $end_date];

if (!empty($kelas_id)) {
    $query .= " AND u.kelas_id = ?";
    $params[] = $kelas_id;
}

$query .= " ORDER BY a.tanggal DESC, k.nama_kelas, u.nama_lengkap";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$absensi_list = $stmt->fetchAll();
?>

<!-- CSS untuk print -->
<style>
@media print {
    .no-print {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>

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
                <div class="card-body">
                    <!-- Filter Section -->
                    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
                        <div class="d-flex align-items-center">
                            <div style="width: 200px; margin-right: 10px;">
                                <label>Periode:</label>
                            </div>
                            <div class="input-group" style="width: 200px;">
                                <input type="date" id="start_date" class="form-control" 
                                    value="<?php echo $start_date; ?>" onchange="filterData()">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">s/d</span>
                                </div>
                                <input type="date" id="end_date" class="form-control" 
                                    value="<?php echo $end_date; ?>" onchange="filterData()">
                            </div>
                            <div style="width: 200px; margin-left: 10px;">
                                <select id="kelas_id" class="form-control" onchange="filterData()">
                                    <option value="">Semua Kelas</option>
                                    <?php foreach($kelas_list as $kelas): ?>
                                    <option value="<?php echo $kelas['id']; ?>" 
                                            <?php echo $kelas_id == $kelas['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($kelas['nama_kelas']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div>
                            <button onclick="exportToExcel()" class="btn btn-success">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                            <button onclick="exportToPDF()" class="btn btn-danger">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </button>
                            <button onclick="window.print()" class="btn btn-info">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="absensiTable">
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
                                <?php 
                                $no = 1;
                                foreach($absensi_list as $absensi): 
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($absensi['tgl']); ?></td>
                                    <td><?php echo htmlspecialchars($absensi['nis']); ?></td>
                                    <td><?php echo htmlspecialchars($absensi['nama_lengkap']); ?></td>
                                    <td><?php echo htmlspecialchars($absensi['nama_kelas']); ?></td>
                                    <td>
                                        <?php 
                                        switch($absensi['status']) {
                                            case 'hadir':
                                                echo '<span class="badge badge-success">Hadir</span>';
                                                break;
                                            case 'izin':
                                                echo '<span class="badge badge-warning">Izin</span>';
                                                break;
                                            case 'sakit':
                                                echo '<span class="badge badge-info">Sakit</span>';
                                                break;
                                            case 'alpha':
                                                echo '<span class="badge badge-danger">Alpha</span>';
                                                break;
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
<script>
function filterData() {
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    const kelas_id = document.getElementById('kelas_id').value;
    
    window.location.href = 'lprn_absensi_sekolah.php?start_date=' + start_date + 
                          '&end_date=' + end_date + 
                          (kelas_id ? '&kelas_id=' + kelas_id : '');
}

function exportToExcel() {
    const table = document.getElementById('absensiTable');
    const wb = XLSX.utils.table_to_book(table, {sheet: "Absensi"});
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    
    XLSX.writeFile(wb, `Laporan_Absensi_${start_date}_sd_${end_date}.xlsx`);
}

function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l', 'mm', 'a4');
    
    // Set margin
    const margin = {
        top: 20,
        left: 20,
        right: 20,
        bottom: 20
    };
    
    // Add current date and time
    const now = new Date();
    const dateStr = now.toLocaleDateString('id-ID');
    const timeStr = now.toLocaleTimeString('id-ID');
    
    // Prepare table data
    const table = document.getElementById('absensiTable');
    const rows = Array.from(table.querySelectorAll('tr'));
    const data = rows.map(row => {
        const cells = Array.from(row.querySelectorAll('th, td'));
        return cells.map(cell => cell.textContent.trim());
    });

    // Add table
    doc.autoTable({
        startY: margin.top + 35,
        head: [data[0]],
        body: data.slice(1),
        theme: 'grid',
        styles: {
            fontSize: 8,
            cellPadding: 2
        },
        headStyles: {
            fillColor: [200, 200, 200],
            textColor: [0, 0, 0],
            fontStyle: 'bold'
        },
        columnStyles: {
            0: { cellWidth: 10 },  // No
            1: { cellWidth: 25 },  // Tanggal
            2: { cellWidth: 25 },  // NIS
            3: { cellWidth: 50 },  // Nama
            4: { cellWidth: 25 },  // Kelas
            5: { cellWidth: 20 }   // Status
        },
        margin: margin,
        didDrawPage: function(data) {
            // Header
            if (data.pageNumber === 1) {
                doc.setFontSize(16);
                doc.setFont(undefined, 'bold');
                doc.text('LAPORAN ABSENSI SEKOLAH', doc.internal.pageSize.width/2, margin.top + 8, { align: 'center' });
                doc.text('SMA DAARUL QUR\'AN', doc.internal.pageSize.width/2, margin.top + 17, { align: 'center' });
                
                doc.setFontSize(10);
                doc.setFont(undefined, 'normal');
                doc.text(`Periode: ${document.getElementById('start_date').value} s/d ${document.getElementById('end_date').value}`, margin.left, margin.top + 25);
                doc.text(`Dicetak pada: ${dateStr}, ${timeStr}`, margin.left, margin.top + 30);
            }
            
            // Footer
            doc.setFontSize(8);
            doc.text(`Halaman ${data.pageNumber}`, doc.internal.pageSize.width - margin.right, doc.internal.pageSize.height - 10, { align: 'right' });
        }
    });

    // Save PDF
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    doc.save(`Laporan_Absensi_${start_date}_sd_${end_date}.pdf`);
}
</script>

<?php include '../templates/footer.php'; ?> 