<?php 
include '../templates/header.php';
include '../templates/sidebar.php';
require_once '../config/database.php';
checkRole(['pengasuhan']);

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

// Query untuk mengambil data absensi
$query = "SELECT s.username as nis, s.nama_lengkap, k.nama_kelas,
          COUNT(CASE WHEN ap.status = 'hadir' THEN 1 END) as hadir,
          COUNT(CASE WHEN ap.status = 'hl' THEN 1 END) as hl,
          COUNT(CASE WHEN ap.status = 'hn' THEN 1 END) as hn,
          COUNT(CASE WHEN ap.status = 'bawabah' THEN 1 END) as bawabah
          FROM users s
          LEFT JOIN kelas k ON s.kelas_id = k.id
          LEFT JOIN absensi_pengasuhan ap ON s.id = ap.user_id 
          AND ap.tanggal BETWEEN :start_date AND :end_date
          WHERE s.role = 'siswa'";

// Filter kelas jika ada
if (isset($_GET['kelas_id']) && !empty($_GET['kelas_id'])) {
    $query .= " AND s.kelas_id = :kelas_id";
}

$query .= " GROUP BY s.id, s.username, s.nama_lengkap, k.nama_kelas
            ORDER BY k.nama_kelas, s.nama_lengkap";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':start_date', $start_date);
$stmt->bindParam(':end_date', $end_date);

if (isset($_GET['kelas_id']) && !empty($_GET['kelas_id'])) {
    $stmt->bindParam(':kelas_id', $_GET['kelas_id']);
}

$stmt->execute();
$absensi_list = $stmt->fetchAll();
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Laporan Absensi Pengasuhan</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Laporan Absensi Pengasuhan</li>
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
                    <h3 class="card-title">Filter Data</h3>
                </div>
                <div class="card-body">
                    <form method="get" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tanggal Mulai</label>
                                    <input type="date" class="form-control" name="start_date" id="start_date" 
                                           value="<?php echo $start_date; ?>" onchange="filterData()">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tanggal Selesai</label>
                                    <input type="date" class="form-control" name="end_date" id="end_date" 
                                           value="<?php echo $end_date; ?>" onchange="filterData()">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Kelas</label>
                                    <select class="form-control" name="kelas_id" id="kelas_id" onchange="filterData()">
                                        <option value="">Semua Kelas</option>
                                        <?php foreach($kelas_list as $kelas): ?>
                                        <option value="<?php echo $kelas['id']; ?>" 
                                            <?php echo isset($_GET['kelas_id']) && $_GET['kelas_id'] == $kelas['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($kelas['nama_kelas']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="button" class="btn btn-success" onclick="exportExcel()">
                                            <i class="fas fa-file-excel"></i> Excel
                                        </button>
                                        <button type="button" class="btn btn-danger" onclick="exportPDF()">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </button>
                                        <button type="button" class="btn btn-info" onclick="printReport()">
                                            <i class="fas fa-print"></i> Print
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIS</th>
                                    <th>Nama Lengkap</th>
                                    <th>Kelas</th>
                                    <th>Hadir</th>
                                    <th>HL</th>
                                    <th>HN</th>
                                    <th>Bawabah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($absensi_list as $index => $a): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($a['nis']); ?></td>
                                    <td><?php echo htmlspecialchars($a['nama_lengkap']); ?></td>
                                    <td><?php echo htmlspecialchars($a['nama_kelas']); ?></td>
                                    <td><?php echo $a['hadir']; ?></td>
                                    <td><?php echo $a['hl']; ?></td>
                                    <td><?php echo $a['hn']; ?></td>
                                    <td><?php echo $a['bawabah']; ?></td>
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

<!-- Script untuk export dan print -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

<script>
function filterData() {
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    const kelas_id = document.getElementById('kelas_id').value;
    
    let url = 'lprn_absensi_pengasuhan.php?';
    if (start_date) url += `start_date=${start_date}&`;
    if (end_date) url += `end_date=${end_date}&`;
    if (kelas_id) url += `kelas_id=${kelas_id}`;
    
    // Hapus & terakhir jika ada
    url = url.replace(/&$/, '');
    
    window.location.href = url;
}

function exportExcel() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = `export_excel.php?start_date=${params.get('start_date') || '<?php echo $start_date; ?>'}&end_date=${params.get('end_date') || '<?php echo $end_date; ?>'}&kelas_id=${params.get('kelas_id') || ''}`;
}

function exportPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    // Set document properties
    doc.setProperties({
        title: 'Laporan Absensi Pengasuhan'
    });
    
    // Add content
    doc.autoTable({
        html: '.table',
        startY: 50,
        styles: {
            fontSize: 8,
            cellPadding: 2
        },
        columnStyles: {
            0: { cellWidth: 10 },  // No
            1: { cellWidth: 25 },  // NIS
            2: { cellWidth: 40 },  // Nama
            3: { cellWidth: 25 },  // Kelas
            4: { cellWidth: 20 },  // Hadir
            5: { cellWidth: 20 },  // HL
            6: { cellWidth: 20 },  // HN
            7: { cellWidth: 20 }   // Bawabah
        },
        headStyles: {
            fillColor: [66, 135, 245],
            textColor: [255, 255, 255],
            halign: 'center'
        },
        didDrawPage: function(data) {
            // Header
            doc.setFontSize(16);
            doc.text('Laporan Absensi Pengasuhan', 15, 20);
            
            // Sub header
            doc.setFontSize(10);
            const today = new Date();
            const dateStr = today.toLocaleDateString('id-ID');
            const timeStr = today.toLocaleTimeString('id-ID');
            
            doc.text(`Periode: ${document.querySelector('[name="start_date"]').value} s/d ${document.querySelector('[name="end_date"]').value}`, 15, 30);
            doc.text(`Dicetak pada: ${dateStr}, ${timeStr}`, 15, 35);
            
            // Footer
            doc.setFontSize(8);
            doc.text(`Halaman ${data.pageNumber}`, doc.internal.pageSize.width - 20, doc.internal.pageSize.height - 10);
        }
    });

    // Save PDF
    const start_date = document.querySelector('[name="start_date"]').value;
    const end_date = document.querySelector('[name="end_date"]').value;
    doc.save(`Laporan_Absensi_Pengasuhan_${start_date}_sd_${end_date}.pdf`);
}

function printReport() {
    window.print();
}
</script>

<?php include '../templates/footer.php'; ?> 