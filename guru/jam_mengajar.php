<?php 
include '../templates/header.php';
include '../templates/sidebar.php';
require_once '../config/database.php';
checkRole(['guru', 'wali_kelas']);

// Filter hari default (semua hari)
$hari = isset($_GET['hari']) ? $_GET['hari'] : '';

// Query untuk mengambil data jam mengajar guru yang login
$query = "SELECT jm.*, u.nama_lengkap, u.role, k.nama_kelas, u.mata_pelajaran,
          CASE jm.hari 
            WHEN 1 THEN 'Senin'
            WHEN 2 THEN 'Selasa'
            WHEN 3 THEN 'Rabu'
            WHEN 4 THEN 'Kamis'
            WHEN 5 THEN 'Jumat'
            WHEN 6 THEN 'Sabtu'
          END as nama_hari
          FROM jam_mengajar jm
          JOIN users u ON jm.guru_id = u.id
          JOIN kelas k ON jm.kelas_id = k.id
          WHERE jm.guru_id = :guru_id";

// Tambahkan filter hari jika ada
if (!empty($hari)) {
    $query .= " AND jm.hari = :hari";
}

$query .= " ORDER BY jm.hari, jm.jam";
$stmt = $pdo->prepare($query);

$stmt->bindValue(':guru_id', $_SESSION['user_id'], PDO::PARAM_INT);
if (!empty($hari)) {
    $stmt->bindValue(':hari', $hari, PDO::PARAM_INT);
}
$stmt->execute();

$jam_mengajar = $stmt->fetchAll();

// Ambil data guru yang login
$stmt = $pdo->prepare("SELECT nama_lengkap, role, mata_pelajaran FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$guru = $stmt->fetch();
?>

<!-- CSS untuk print dan tabel sama seperti di admin -->
<style>
/* ... CSS yang sama seperti di admin/jam_mengajar.php ... */
</style>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Jadwal Mengajar</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Jadwal Mengajar</li>
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
                    <div class="row">
                        <div class="col-md-8">
                            <select class="form-control" id="hariFilter" onchange="filterHari(this.value)">
                                <option value="">Semua Hari</option>
                                <option value="1" <?php echo $hari == '1' ? 'selected' : ''; ?>>Senin</option>
                                <option value="2" <?php echo $hari == '2' ? 'selected' : ''; ?>>Selasa</option>
                                <option value="3" <?php echo $hari == '3' ? 'selected' : ''; ?>>Rabu</option>
                                <option value="4" <?php echo $hari == '4' ? 'selected' : ''; ?>>Kamis</option>
                                <option value="5" <?php echo $hari == '5' ? 'selected' : ''; ?>>Jumat</option>
                                <option value="6" <?php echo $hari == '6' ? 'selected' : ''; ?>>Sabtu</option>
                            </select>
                        </div>
                        <div class="col-md-4 text-right">
                            <button type="button" class="btn btn-danger" onclick="exportToPDF()">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Hari</th>
                                <th>Jam Ke</th>
                                <th>Kelas</th>
                                <th>Mata Pelajaran</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            foreach($jam_mengajar as $jm): 
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($jm['nama_hari']); ?></td>
                                <td><?php echo htmlspecialchars($jm['jam']); ?></td>
                                <td><?php echo htmlspecialchars($jm['nama_kelas']); ?></td>
                                <td><?php echo htmlspecialchars($jm['mata_pelajaran']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Script PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

<script>
function filterHari(hari) {
    window.location.href = 'jam_mengajar.php' + (hari ? '?hari=' + hari : '');
}

function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'a4');

    // Margin dalam mm (A4: 210 x 297 mm)
    const margin = {
        top: 13,    // 1 cm
        right: 20,  // 2 cm
        bottom: 15, // 1.5 cm
        left: 22    // 2 cm
    };

    // Set font size dan style untuk judul
    doc.setFontSize(16);
    doc.setFont(undefined, 'bold');
    
    // Add title
    doc.text('JADWAL MENGAJAR', doc.internal.pageSize.width/2, margin.top + 8, { align: 'center' });
    doc.text('SMA DAARUL QUR\'AN', doc.internal.pageSize.width/2, margin.top + 17, { align: 'center' });
    
    // Add sub judul (info guru)
    
    
    // Add info guru
    doc.setFontSize(11);
    doc.setFont(undefined, 'normal');
    doc.text('Nama Guru', margin.left, margin.top + 32);
    doc.text(': <?php echo $guru['nama_lengkap']; ?>', margin.left + 27, margin.top + 32);
    doc.text('Mata Pelajaran', margin.left, margin.top + 39);
    doc.text(': <?php echo $guru['mata_pelajaran']; ?>', margin.left + 27, margin.top + 39);
    
    // Add waktu cetak
    doc.setFontSize(10);
    const now = new Date();
    const dateStr = now.toLocaleDateString('id-ID', { 
        day: '2-digit', 
        month: 'long', 
        year: 'numeric'
    });
    const timeStr = now.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit'
    });
    

    // Prepare table data
    const table = document.querySelector("table");
    const rows = Array.from(table.querySelectorAll('tr'));
    const data = rows.map(row => {
        const cells = Array.from(row.querySelectorAll('th, td'));
        return cells.map(cell => cell.innerText.replace(/\s+/g, ' ').trim());
    });

    // Add table
    doc.autoTable({
        startY: margin.top + 48,
        head: [data[0]],
        body: data.slice(1),
        theme: 'grid',
        styles: {
            fontSize: 9,
            cellPadding: 2,
            textColor: [0, 0, 0],
            lineColor: [0, 0, 0],
            lineWidth: 0.1,
            halign: 'center',
            valign: 'middle'
        },
        headStyles: {
            fillColor: [200, 200, 200],
            textColor: [0, 0, 0],
            fontStyle: 'bold'
        },
        columnStyles: {
            0: { cellWidth: 17 },  // No
            1: { cellWidth: 35 },  // Hari
            2: { cellWidth: 30 },  // Jam Ke
            3: { cellWidth: 40 },  // Kelas
            4: { cellWidth: 43 }   // Mata Pelajaran
        },
        margin: margin
    });

    // Save PDF
    const hari = document.getElementById('hariFilter').value;
    const hariText = hari ? document.getElementById('hariFilter').options[document.getElementById('hariFilter').selectedIndex].text : '';
    const hariFile = hari ? '_' + hariText : '';
    doc.save('Jadwal_Mengajar_<?php echo str_replace(" ", "_", $guru["nama_lengkap"]); ?>' + hariFile + '.pdf');
}
</script>

<?php include '../templates/footer.php'; ?> 