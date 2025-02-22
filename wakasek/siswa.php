<?php 
// Tambahkan di bagian atas file setelah include
$default_image = '../assets/img/foto-awal.jpg';
if (!file_exists($default_image)) {
    error_log("Default image not found at: " . realpath($default_image));
}

define('DEFAULT_IMAGE', realpath(__DIR__ . '/../assets/img/foto-awal.jpg'));

include '../templates/header.php';
include '../templates/sidebar.php';
require_once '../config/database.php';
checkRole(['wakasek']);

// Filter kelas
$kelas_id = isset($_GET['kelas_id']) ? $_GET['kelas_id'] : '';

// Query untuk mengambil daftar kelas
$query = "SELECT * FROM kelas ORDER BY nama_kelas";
$stmt = $pdo->query($query);
$kelas_list = $stmt->fetchAll();

// Query untuk mengambil data siswa
$query = "SELECT s.*, k.nama_kelas 
          FROM users s
          LEFT JOIN kelas k ON s.kelas_id = k.id
          WHERE s.role = 'siswa'";
if (!empty($kelas_id)) {
    $query .= " AND s.kelas_id = :kelas_id";
}
$query .= " ORDER BY k.nama_kelas, s.nama_lengkap";

$stmt = $pdo->prepare($query);
if (!empty($kelas_id)) {
    $stmt->bindParam(':kelas_id', $kelas_id);
}
$stmt->execute();
$siswa_list = $stmt->fetchAll();

function debug_image_path($path) {
    if (file_exists($path)) {
        error_log("File exists: " . realpath($path));
        error_log("File permissions: " . decoct(fileperms($path) & 0777));
    } else {
        error_log("File not found: " . $path);
        error_log("Current script path: " . realpath(__DIR__));
    }
}
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Data Siswa</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Data Siswa</li>
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
                            <div class="input-group">
                                <select class="form-control" id="kelasFilter" onchange="filterData()">
                                    <option value="">Semua Kelas</option>
                                    <?php foreach($kelas_list as $kelas): ?>
                                        <option value="<?php echo $kelas['id']; ?>" <?php echo $kelas_id == $kelas['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($kelas['nama_kelas']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" class="form-control" id="searchInput" onkeyup="searchTable()" placeholder="Cari nama...">
                            </div>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#uploadZipModal">
                                <i class="fas fa-upload"></i> Upload Foto
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-bordered table-hover" id="siswaTable">
                        <thead>
                            <tr class="text-center">
                                <th width="5%">No</th>
                                <th width="10%">Foto</th>
                                <th>NIS</th>
                                <th>Nama Lengkap</th>
                                <th>Kelas</th>
                                <th>Jenis Kelamin</th>
                                <th style="width: 100px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            foreach($siswa_list as $siswa): 
                                // Tambahkan debug untuk memeriksa path foto
                                $foto_path = !empty($siswa['foto']) ? 
                                    '../assets/img/profile/' . $siswa['foto'] : 
                                    (file_exists(DEFAULT_IMAGE) ? '../assets/img/foto-awal.jpg' : 'https://via.placeholder.com/50');
                                debug_image_path($foto_path);
                                // Tambahkan pengecekan file exists
                                if (!file_exists($foto_path)) {
                                    $foto_path = '../assets/img/foto-awal.jpg'; // Path absolut ke foto default
                                }
                            ?>
                            <tr>
                                <td class="text-center align-middle"><?php echo $no++; ?></td>
                                <td class="text-center">
                                    <img src="<?php echo $foto_path; ?>" alt="Foto Siswa" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                </td>
                                <td class="align-middle"><?php echo htmlspecialchars($siswa['username']); ?></td>
                                <td class="align-middle"><?php echo htmlspecialchars($siswa['nama_lengkap']); ?></td>
                                <td class="text-center align-middle"><?php echo htmlspecialchars($siswa['nama_kelas'] ?? 'Belum ditentukan'); ?></td>
                                <td class="text-center align-middle"><?php echo $siswa['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                                <td class="text-center">
                                    <?php if (empty($siswa['foto'])): ?>
                                    <button type="button" class="btn btn-warning btn-sm" onclick="uploadFoto('<?php echo $siswa['username']; ?>')">
                                        <i class="fas fa-upload"></i> Upload Foto
                                    </button>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-info btn-sm" onclick="editSiswa(<?php echo htmlspecialchars(json_encode($siswa)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteSiswa('<?php echo $siswa['id']; ?>', '<?php echo htmlspecialchars($siswa['nama_lengkap']); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
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

<!-- Update modal upload foto -->
<div class="modal fade" id="uploadModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Upload Foto Siswa</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="upload_foto.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="nis" id="upload_nis">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Foto</label>
                        <input type="file" class="form-control-file" name="foto" accept="image/*" required>
                        <small class="form-text text-muted">Upload foto (JPG, JPEG, PNG)</small>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Tambahkan modal upload ZIP -->
<div class="modal fade" id="uploadZipModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Upload Foto Siswa (ZIP)</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="upload_foto_zip.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label>File ZIP</label>
                        <input type="file" class="form-control-file" name="foto_zip" accept=".zip" required>
                        <small class="form-text text-muted">
                            Upload file ZIP berisi foto siswa. Nama file foto harus sesuai dengan NIS siswa (contoh: 1234.jpg).
                            <br>Format file: JPG/JPEG/PNG
                            <br>Nama file harus sesuai NIS siswa
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Tambahkan modal edit siswa -->
<div class="modal fade" id="editModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Data Siswa</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="edit_siswa.php" method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>NIS</label>
                        <input type="text" class="form-control" name="username" id="edit_username" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama_lengkap" id="edit_nama_lengkap" required>
                    </div>
                    <div class="form-group">
                        <label>Kelas</label>
                        <select class="form-control" name="kelas_id" id="edit_kelas_id" required>
                            <option value="">Pilih Kelas</option>
                            <?php foreach($kelas_list as $kelas): ?>
                                <option value="<?php echo $kelas['id']; ?>">
                                    <?php echo htmlspecialchars($kelas['nama_kelas']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jenis Kelamin</label>
                        <select class="form-control" name="jenis_kelamin" id="edit_jenis_kelamin" required>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Password Baru</label>
                        <input type="password" class="form-control" name="password" placeholder="Kosongkan jika tidak ingin mengubah password">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form untuk delete -->
<form id="deleteForm" action="delete_siswa.php" method="POST" style="display: none;">
    <input type="hidden" name="id" id="delete_id">
</form>

<!-- Tambahkan alert untuk menampilkan pesan sukses/error -->
<?php if(isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?php 
    echo $_SESSION['success'];
    unset($_SESSION['success']);
    ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?php 
    echo $_SESSION['error'];
    unset($_SESSION['error']);
    ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<script>
// Fungsi untuk filter kelas
function filterData() {
    const kelas = document.getElementById('kelasFilter').value;
    window.location.href = 'siswa.php' + (kelas ? '?kelas_id=' + kelas : '');
}

// Fungsi untuk pencarian nama realtime
function searchTable() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const table = document.getElementById('siswaTable');
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const namaSiswa = rows[i].getElementsByTagName('td')[3].textContent.toLowerCase();
        if (namaSiswa.includes(input)) {
            rows[i].style.display = '';
        } else {
            rows[i].style.display = 'none';
        }
    }
}

// Fungsi export PDF
function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l', 'mm', 'a4');

    // Margin
    const margin = {
        top: 13,
        right: 20,
        bottom: 15,
        left: 22
    };

    // Judul
    doc.setFontSize(16);
    doc.setFont(undefined, 'bold');
    doc.text('DATA SISWA', doc.internal.pageSize.width/2, margin.top + 8, { align: 'center' });
    doc.text('SMA DAARUL QUR\'AN', doc.internal.pageSize.width/2, margin.top + 17, { align: 'center' });

    // Filter info
    doc.setFontSize(11);
    doc.setFont(undefined, 'normal');
    const kelasFilter = document.getElementById('kelasFilter');
    const kelasText = kelasFilter.options[kelasFilter.selectedIndex].text;
    doc.text('Kelas: ' + (kelasFilter.value ? kelasText : 'Semua Kelas'), margin.left, margin.top + 30);

    // Prepare table data
    const table = document.getElementById('siswaTable');
    const rows = Array.from(table.querySelectorAll('tr:not([style*="display: none"])'));
    const data = rows.map(row => {
        const cells = Array.from(row.querySelectorAll('th, td'));
        return cells.map((cell, index) => {
            // Skip kolom foto
            if (index === 1) return '';
            return cell.textContent.trim();
        }).filter((_, index) => index !== 1); // Hapus kolom foto dari data
    });

    // Add table
    doc.autoTable({
        startY: margin.top + 35,
        head: [['No', 'NIS', 'Nama Lengkap', 'Kelas', 'Jenis Kelamin']],
        body: data.slice(1),
        theme: 'grid',
        styles: {
            fontSize: 9,
            cellPadding: 2,
            textColor: [0, 0, 0],
            lineColor: [0, 0, 0],
            lineWidth: 0.1
        },
        headStyles: {
            fillColor: [200, 200, 200],
            textColor: [0, 0, 0],
            fontStyle: 'bold'
        },
        columnStyles: {
            0: { cellWidth: 15 },
            1: { cellWidth: 30 },
            2: { cellWidth: 60 },
            3: { cellWidth: 40 },
            4: { cellWidth: 30 }
        },
        margin: margin
    });

    // Save PDF
    doc.save('Data_Siswa_SMA_Daarul_Quran.pdf');
}

function uploadFoto(nis) {
    document.getElementById('upload_nis').value = nis;
    $('#uploadModal').modal('show');
}

// Fungsi untuk edit siswa
function editSiswa(siswa) {
    document.getElementById('edit_id').value = siswa.id;
    document.getElementById('edit_username').value = siswa.username;
    document.getElementById('edit_nama_lengkap').value = siswa.nama_lengkap;
    document.getElementById('edit_kelas_id').value = siswa.kelas_id;
    document.getElementById('edit_jenis_kelamin').value = siswa.jenis_kelamin;
    $('#editModal').modal('show');
}

// Fungsi untuk delete siswa
function deleteSiswa(id, nama) {
    Swal.fire({
        title: 'Hapus Siswa?',
        text: `Anda yakin ingin menghapus data siswa "${nama}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete_id').value = id;
            document.getElementById('deleteForm').submit();
        }
    });
}
</script>

<!-- Script PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

<?php include '../templates/footer.php'; ?> 