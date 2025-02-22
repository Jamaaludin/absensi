<?php 
include '../templates/header.php';
include '../templates/sidebar.php';
require_once '../config/database.php';
checkRole(['admin']);

// Filter hari default (semua hari)
$hari = isset($_GET['hari']) ? $_GET['hari'] : '';

// Query untuk mengambil data jadwal mengajar
$query = "SELECT jm.id, jm.hari, jm.jam, jm.mata_pelajaran,
          u.nama_lengkap as nama_guru, 
          u.role,
          k.nama_kelas,
          jm.mata_pelajaran as mata_pelajaran_mengajar
          FROM jam_mengajar jm
          JOIN users u ON jm.guru_id = u.id
          JOIN kelas k ON jm.kelas_id = k.id
          GROUP BY jm.id
          ORDER BY u.nama_lengkap, jm.hari, jm.jam";

// Tambahkan filter hari jika ada
if (!empty($hari)) {
    $query .= " WHERE jm.hari = :hari";
}

$stmt = $pdo->prepare($query);

if (!empty($hari)) {
    $stmt->bindValue(':hari', $hari, PDO::PARAM_INT);
}
$stmt->execute();

$jadwal = $stmt->fetchAll();
?>

<!-- CSS untuk print dan tabel -->
<style>
@media print {
    /* Sembunyikan elemen yang tidak perlu di print */
    .main-header,
    .main-sidebar,
    .card-header,
    .breadcrumb,
    .btn,
    footer,
    .no-print,
    .badge {
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
        margin-bottom: 20px;
        border-bottom: 2px solid #000;
        padding-bottom: 10px;
    }

    .print-header h3 {
        margin: 0;
        font-size: 20px;
        font-weight: bold;
    }

    .print-header p {
        margin: 5px 0 0 0;
        font-size: 16px;
    }

    /* Style untuk tabel saat print */
    .table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .table th,
    .table td {
        border: 1px solid #000 !important;
        padding: 8px;
    }
}

/* Style untuk tabel */
.table th, 
.table td {
    vertical-align: middle !important;
    text-align: center !important;
}

/* Khusus untuk kolom nama guru tetap rata kiri */
.table td:nth-child(2) {
    text-align: left !important;
}
</style>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Data Jam Mengajar</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Jam Mengajar</li>
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
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchInput" 
                                       placeholder="Cari nama guru..." onkeyup="filterTable()">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
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
                            <button type="button" class="btn btn-danger mr-2" onclick="exportToPDF()">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </button>
                            <button type="button" class="btn btn-info" onclick="window.print()">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <!-- Print Header -->
                    <div class="d-none d-print-block print-header">
                        <h3>DATA JAM MENGAJAR</h3>
                        <?php if (!empty($hari)): ?>
                            <p>Hari: <?php echo ['', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][$hari]; ?></p>
                        <?php else: ?>
                            <p>Semua Hari</p>
                        <?php endif; ?>
                    </div>

                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama Guru</th>
                                <th>Mata Pelajaran</th>
                                <th>Hari</th>
                                <th>Jam Ke</th>
                                <th>Kelas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            foreach($jadwal as $j): 
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($j['nama_guru']); ?>
                                    <span class="badge badge-<?php echo isset($j['role']) && $j['role'] == 'wali_kelas' ? 'success' : 'secondary'; ?> d-print-none">
                                        <?php echo isset($j['role']) && $j['role'] == 'wali_kelas' ? 'Wali Kelas' : 'Guru'; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($j['mata_pelajaran_mengajar']); ?></td>
                                <td><?php 
                                    $hari = [
                                        1 => 'Senin',
                                        2 => 'Selasa',
                                        3 => 'Rabu',
                                        4 => 'Kamis',
                                        5 => 'Jumat',
                                        6 => 'Sabtu'
                                    ];
                                    echo $hari[$j['hari']] ?? '-';
                                ?></td>
                                <td><?php echo htmlspecialchars($j['jam']); ?></td>
                                <td><?php echo htmlspecialchars($j['nama_kelas']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

<script>
function filterTable() {
    var input = document.getElementById("searchInput");
    var filter = input.value.toLowerCase();
    var table = document.querySelector("table");
    var tr = table.getElementsByTagName("tr");
    var visibleCount = 0;

    for (var i = 1; i < tr.length; i++) {
        var td = tr[i].getElementsByTagName("td")[1]; // Index 1 adalah kolom Nama Guru
        if (td) {
            var txtValue = td.textContent || td.innerText;
            if (txtValue.toLowerCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
                visibleCount++;
                // Update nomor urut yang terlihat
                tr[i].getElementsByTagName("td")[0].textContent = visibleCount;
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}

function filterHari(hari) {
    window.location.href = 'jam_mengajar.php' + (hari ? '?hari=' + hari : '');
}

function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'a4'); // portrait, A4

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
    
    // Add title (hanya di halaman pertama)
    doc.text('JADWAL MENGAJAR', doc.internal.pageSize.width/2, margin.top + 8, { align: 'center' });
    doc.text('SMA DAARUL QUR\'AN', doc.internal.pageSize.width/2, margin.top + 17, { align: 'center' });
    
    // Add waktu cetak
    doc.setFontSize(10);
    doc.setFont(undefined, 'normal');
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
    doc.text(`Dicetak pada: ${dateStr}, ${timeStr}`, margin.left, margin.top + 35);

    // Prepare table data
    const table = document.querySelector("table");
    const rows = Array.from(table.querySelectorAll('tr'));
    const data = rows.map(row => {
        const cells = Array.from(row.querySelectorAll('th, td'));
        return cells.map(cell => cell.innerText.replace(/\s+/g, ' ').trim());
    });

    // Remove badge text from data
    data.forEach(row => {
        if (row[1]) {
            row[1] = row[1].split('Wali Kelas')[0].split('Guru')[0].trim();
        }
    });

    // Add table
    doc.autoTable({
        startY: margin.top + 40,
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
            fontStyle: 'bold',
            halign: 'center'
        },
        columnStyles: {
            0: { cellWidth: 10 },  // No
            1: { cellWidth: 45, halign: 'left' }, // Nama Guru
            2: { cellWidth: 35 },  // Mata Pelajaran
            3: { cellWidth: 25 },  // Hari
            4: { cellWidth: 15 },  // Jam Ke
            5: { cellWidth: 35 }   // Kelas
        },
        alternateRowStyles: {
            fillColor: [245, 245, 245]
        },
        margin: margin,
        didDrawPage: function(data) {
            // Header hanya di halaman pertama
            if (data.pageNumber === 1) {
                doc.setFontSize(16);
                doc.setFont(undefined, 'bold');
                doc.text('JADWAL MENGAJAR', doc.internal.pageSize.width/2, margin.top + 8, { align: 'center' });
                doc.text('SMA DAARUL QUR\'AN', doc.internal.pageSize.width/2, margin.top + 17, { align: 'center' });
                
                doc.setFontSize(10);
                doc.setFont(undefined, 'normal');
                doc.text(`Dicetak pada: ${dateStr}, ${timeStr}`, margin.left, margin.top + 35);
            }
        },
        willDrawCell: function(data) {
            // Alignment untuk setiap kolom
            if (data.section === 'body') {
                if (data.column.index === 1) {
                    data.cell.styles.halign = 'left';
                } else {
                    data.cell.styles.halign = 'center';
                }
            }
        }
    });

    // Save PDF
    const hari = document.getElementById('hariFilter').value;
    const hariText = hari ? document.getElementById('hariFilter').options[document.getElementById('hariFilter').selectedIndex].text : '';
    const hariFile = hari ? '_' + hariText : '';
    doc.save('Jadwal_Mengajar' + hariFile + '.pdf');
}
</script>

<?php include '../templates/footer.php'; ?> 