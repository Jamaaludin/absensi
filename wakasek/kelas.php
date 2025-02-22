<?php 
include '../templates/header.php';
include '../templates/sidebar.php';
require_once '../config/database.php';
checkRole(['wakasek']);

// Query untuk mengambil data kelas dengan wali kelas dan jumlah siswa
$query = "SELECT k.*, 
          u1.nama_lengkap as wali_kelas,
          (SELECT COUNT(*) FROM users u2 WHERE u2.kelas_id = k.id AND u2.role = 'siswa') as jumlah_siswa
          FROM kelas k
          LEFT JOIN users u1 ON k.wali_kelas_id = u1.id
          ORDER BY k.nama_kelas ASC";
$stmt = $pdo->query($query);
$kelas_list = $stmt->fetchAll();
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Data Kelas</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Data Kelas</li>
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
                        <div class="col-md-6">
                            <h3 class="card-title">Daftar Kelas</h3>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="button" class="btn btn-danger" onclick="exportToPDF()">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr class="text-center">
                                <th style="width: 50px">No</th>
                                <th>Nama Kelas</th>
                                <th>Wali Kelas</th>
                                <th>Jumlah Siswa</th>
                                <th>Tahun Ajaran</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            foreach($kelas_list as $kelas): 
                            ?>
                            <tr>
                                <td class="text-center"><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($kelas['nama_kelas']); ?></td>
                                <td><?php echo htmlspecialchars($kelas['wali_kelas'] ?? 'Belum ditentukan'); ?></td>
                                <td class="text-center"><?php echo $kelas['jumlah_siswa']; ?> Siswa</td>
                                <td class="text-center"><?php echo htmlspecialchars($kelas['tahun_ajaran']); ?></td>
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
function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'a4');

    // Margin dalam mm (A4: 210 x 297 mm)
    const margin = {
        top: 13,
        right: 20,
        bottom: 15,
        left: 22
    };

    // Set font size dan style untuk judul
    doc.setFontSize(16);
    doc.setFont(undefined, 'bold');
    
    // Add title
    doc.text('DATA KELAS', doc.internal.pageSize.width/2, margin.top + 8, { align: 'center' });
    doc.text('SMA DAARUL QUR\'AN', doc.internal.pageSize.width/2, margin.top + 17, { align: 'center' });
    
    // Add tahun ajaran yang aktif
    doc.setFontSize(11);
    doc.setFont(undefined, 'normal');
    doc.text('Tahun Ajaran: <?php echo $kelas_list[0]['tahun_ajaran'] ?? '-'; ?>', margin.left, margin.top + 30);

    // Prepare table data
    const table = document.querySelector("table");
    const rows = Array.from(table.querySelectorAll('tr'));
    const data = rows.map(row => {
        const cells = Array.from(row.querySelectorAll('th, td'));
        return cells.map(cell => cell.innerText.replace(/\s+/g, ' ').trim());
    });

    // Add table
    doc.autoTable({
        startY: margin.top + 35,
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
            0: { cellWidth: 15 },  // No
            1: { cellWidth: 40 },  // Nama Kelas
            2: { cellWidth: 50 },  // Wali Kelas
            3: { cellWidth: 30 },  // Jumlah Siswa
            4: { cellWidth: 30 }   // Tahun Ajaran
        },
        margin: margin
    });

    // Save PDF
    doc.save('Data_Kelas_SMA_Daarul_Quran.pdf');
}
</script>

<?php include '../templates/footer.php'; ?> 