<?php 
include '../templates/header.php';
include '../templates/sidebar.php';
require_once '../config/database.php';
checkRole(['wakasek']);

// Filter hari default (semua hari)
$hari = isset($_GET['hari']) ? $_GET['hari'] : '';

// Query untuk mengambil semua data jam mengajar
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
          JOIN kelas k ON jm.kelas_id = k.id";

// Tambahkan filter hari jika ada
if (!empty($hari)) {
    $query .= " WHERE jm.hari = :hari";
}

$query .= " ORDER BY jm.hari, jm.jam, u.nama_lengkap";
$stmt = $pdo->prepare($query);

if (!empty($hari)) {
    $stmt->bindValue(':hari', $hari, PDO::PARAM_INT);
}
$stmt->execute();

$jam_mengajar = $stmt->fetchAll();
?>

<!-- CSS untuk print -->
<style>
@media print {
    .main-header,
    .main-sidebar,
    .btn,
    .filter-section {
        display: none !important;
    }
    .content-wrapper {
        margin: 0 !important;
        padding: 0 !important;
    }
    .main-footer {
        display: none;
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
                        <div class="col-md-6">
                            <h3 class="card-title">Daftar Jadwal Mengajar</h3>
                        </div>
                        <div class="col-md-6 text-right">
                            <button onclick="printJadwal()" class="btn btn-info">
                                <i class="fas fa-print"></i> Print
                            </button>
                            <button onclick="exportToPDF()" class="btn btn-danger">
                                <i class="fas fa-file-pdf"></i> Export PDF
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Section -->
                    <div class="row mb-3 filter-section">
                        <div class="col-md-3">
                            <select id="hariFilter" class="form-control" onchange="filterJadwal()">
                                <option value="">Semua Hari</option>
                                <option value="1" <?php echo $hari == '1' ? 'selected' : ''; ?>>Senin</option>
                                <option value="2" <?php echo $hari == '2' ? 'selected' : ''; ?>>Selasa</option>
                                <option value="3" <?php echo $hari == '3' ? 'selected' : ''; ?>>Rabu</option>
                                <option value="4" <?php echo $hari == '4' ? 'selected' : ''; ?>>Kamis</option>
                                <option value="5" <?php echo $hari == '5' ? 'selected' : ''; ?>>Jumat</option>
                                <option value="6" <?php echo $hari == '6' ? 'selected' : ''; ?>>Sabtu</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" id="searchInput" class="form-control" 
                                   placeholder="Cari Nama Guru..." 
                                   onkeyup="searchGuru()">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="jadwalTable">
                            <thead>
                                <tr>
                                    <th style="width: 50px">No</th>
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
                                foreach($jam_mengajar as $jm): 
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($jm['nama_lengkap']); ?></td>
                                    <td><?php echo htmlspecialchars($jm['mata_pelajaran']); ?></td>
                                    <td><?php echo htmlspecialchars($jm['nama_hari']); ?></td>
                                    <td><?php echo htmlspecialchars($jm['jam']); ?></td>
                                    <td><?php echo htmlspecialchars($jm['nama_kelas']); ?></td>
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

<script>
function searchGuru() {
    var input, filter, table, tr, td, i, txtValue;
    input = document.getElementById("searchInput");
    filter = input.value.toLowerCase();
    table = document.getElementById("jadwalTable");
    tr = table.getElementsByTagName("tr");

    for (i = 0; i < tr.length; i++) {
        td = tr[i].getElementsByTagName("td")[1]; // index 1 adalah kolom Nama Guru
        if (td) {
            txtValue = td.textContent || td.innerText;
            if (txtValue.toLowerCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}

function filterJadwal() {
    const hari = document.getElementById('hariFilter').value;
    window.location.href = 'jam_mengajar.php' + (hari ? '?hari=' + hari : '');
}

function printJadwal() {
    window.print();
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
    const table = document.getElementById('jadwalTable');
    const rows = Array.from(table.querySelectorAll('tr'));
    const data = rows.map(row => {
        const cells = Array.from(row.querySelectorAll('th, td'));
        return cells.map(cell => cell.textContent.replace(/\s+/g, ' ').trim());
    });

    // Add table
    doc.autoTable({
        startY: margin.top + 35,
        head: [data[0]],
        body: data.slice(1),
        theme: 'grid',
        styles: {
            fontSize: 8,
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