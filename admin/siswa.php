<?php 
include '../templates/header.php';
include '../templates/sidebar.php';
require_once '../config/database.php';
checkRole(['admin']);

// Inisialisasi variabel
$success_msg = '';
$error_msg = '';

// Proses CRUD
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        if (isset($_POST['action'])) {
            switch($_POST['action']) {
                case 'add':
                    $username = $_POST['username'];
                    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $nama_lengkap = $_POST['nama_lengkap'];
                    $kelas_id = $_POST['kelas_id'];
                    $nis = $_POST['nis'];
                    $jenis_kelamin = $_POST['jenis_kelamin'];
                    $alamat = $_POST['alamat'];
                    $no_telp = $_POST['no_telp'];
                    
                    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, nama_lengkap, kelas_id, nis, jenis_kelamin, alamat, no_telp) 
                                         VALUES (?, ?, 'siswa', ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$username, $password, $nama_lengkap, $kelas_id, $nis, $jenis_kelamin, $alamat, $no_telp]);
                    $success_msg = "Data siswa berhasil ditambahkan!";
                    break;
                    
                case 'edit':
                    $id = $_POST['id'];
                    $nama_lengkap = $_POST['nama_lengkap'];
                    $kelas_id = $_POST['kelas_id'];
                    $nis = $_POST['nis'];
                    $jenis_kelamin = $_POST['jenis_kelamin'];
                    $alamat = $_POST['alamat'];
                    $no_telp = $_POST['no_telp'];
                    
                    if (!empty($_POST['password'])) {
                        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET nama_lengkap=?, kelas_id=?, nis=?, 
                                            jenis_kelamin=?, alamat=?, no_telp=?, password=? WHERE id=?");
                        $stmt->execute([$nama_lengkap, $kelas_id, $nis, $jenis_kelamin, $alamat, $no_telp, $password, $id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET nama_lengkap=?, kelas_id=?, nis=?, 
                                            jenis_kelamin=?, alamat=?, no_telp=? WHERE id=?");
                        $stmt->execute([$nama_lengkap, $kelas_id, $nis, $jenis_kelamin, $alamat, $no_telp, $id]);
                    }
                    $success_msg = "Data siswa berhasil diupdate!";
                    break;
                    
                case 'delete':
                    $id = $_POST['id'];
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
                    $stmt->execute([$id]);
                    $success_msg = "Data siswa berhasil dihapus!";
                    break;
            }
            $pdo->commit();
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_msg = "Error: " . $e->getMessage();
    }
}

// Ambil daftar kelas
$stmt = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas");
$kelas_list = $stmt->fetchAll();

// Ambil daftar siswa
$stmt = $pdo->query("SELECT u.*, k.nama_kelas 
                     FROM users u 
                     LEFT JOIN kelas k ON u.kelas_id = k.id 
                     WHERE u.role = 'siswa' 
                     ORDER BY k.nama_kelas, u.nama_lengkap");
$siswa = $stmt->fetchAll();
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Manajemen Data Siswa</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Manajemen Siswa</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <?php if ($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_msg; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>

            <?php if ($error_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_msg; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchInput" 
                                       placeholder="Cari berdasarkan nama lengkap..." onkeyup="filterSiswa()">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-tambah">
                                <i class="fas fa-plus"></i> Tambah
                            </button>
                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modal-import">
                                <i class="fas fa-file-import"></i> Import CSV
                            </button>
                            <a href="siswa_export_csv.php" class="btn btn-info btn-sm">
                                <i class="fas fa-download"></i> Download Format CSV
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIS</th>
                                <th>Nama Lengkap</th>
                                <th>Kelas</th>
                                <th>Jenis Kelamin</th>
                                <th>No. Telp</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($siswa as $index => $s): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($s['nis']); ?></td>
                                <td><?php echo htmlspecialchars($s['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars($s['nama_kelas']); ?></td>
                                <td><?php echo htmlspecialchars($s['jenis_kelamin']); ?></td>
                                <td><?php echo htmlspecialchars($s['no_telp']); ?></td>
                                <td>
                                    <button type="button" class="btn btn-info btn-sm" onclick="viewSiswa(<?php echo htmlspecialchars(json_encode($s)); ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-warning btn-sm" onclick="editSiswa(<?php echo htmlspecialchars(json_encode($s)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteSiswa(<?php echo $s['id']; ?>, '<?php echo $s['nama_lengkap']; ?>')">
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

<!-- Modal Tambah Siswa -->
<div class="modal fade" id="modal-tambah">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Siswa Baru</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label>NIS</label>
                        <input type="text" class="form-control" name="nis" required>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama_lengkap" required>
                    </div>
                    <div class="form-group">
                        <label>Kelas</label>
                        <select class="form-control" name="kelas_id" required>
                            <option value="">Pilih Kelas</option>
                            <?php foreach($kelas_list as $k): ?>
                            <option value="<?php echo $k['id']; ?>"><?php echo htmlspecialchars($k['nama_kelas']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jenis Kelamin</label>
                        <select class="form-control" name="jenis_kelamin" required>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea class="form-control" name="alamat" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>No. Telp</label>
                        <input type="text" class="form-control" name="no_telp">
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Siswa -->
<div class="modal fade" id="editSiswaModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Data Siswa</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label>NIS</label>
                        <input type="text" class="form-control" name="nis" id="edit_nis" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama_lengkap" id="edit_nama_lengkap" required>
                    </div>
                    <div class="form-group">
                        <label>Password (Kosongkan jika tidak diubah)</label>
                        <input type="password" class="form-control" name="password">
                    </div>
                    <div class="form-group">
                        <label>Kelas</label>
                        <select class="form-control" name="kelas_id" id="edit_kelas_id" required>
                            <option value="">Pilih Kelas</option>
                            <?php foreach($kelas_list as $k): ?>
                            <option value="<?php echo $k['id']; ?>"><?php echo htmlspecialchars($k['nama_kelas']); ?></option>
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
                        <label>Alamat</label>
                        <textarea class="form-control" name="alamat" id="edit_alamat" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>No. Telp</label>
                        <input type="text" class="form-control" name="no_telp" id="edit_no_telp">
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form Hapus Siswa -->
<form id="deleteSiswaForm" action="" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
</form>

<!-- Modal Import -->
<div class="modal fade" id="modal-import">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Import Data Siswa</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="siswa_import_csv.php" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label>File CSV</label>
                        <input type="file" name="file" class="form-control" accept=".csv" required>
                        <small class="text-muted">Format: No, NIS, Nama Lengkap, Kelas, Jenis Kelamin, Alamat, No Tlp</small>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal View Siswa -->
<div class="modal fade" id="viewSiswaModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Detail Siswa</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="30%">NIS</th>
                        <td id="view_nis"></td>
                    </tr>
                    <tr>
                        <th>Nama Lengkap</th>
                        <td id="view_nama_lengkap"></td>
                    </tr>
                    <tr>
                        <th>Kelas</th>
                        <td id="view_kelas"></td>
                    </tr>
                    <tr>
                        <th>Jenis Kelamin</th>
                        <td id="view_jenis_kelamin"></td>
                    </tr>
                    <tr>
                        <th>Alamat</th>
                        <td id="view_alamat"></td>
                    </tr>
                    <tr>
                        <th>No. Telp</th>
                        <td id="view_no_telp"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function editSiswa(siswa) {
    document.getElementById('edit_id').value = siswa.id;
    document.getElementById('edit_nis').value = siswa.nis;
    document.getElementById('edit_nama_lengkap').value = siswa.nama_lengkap;
    document.getElementById('edit_kelas_id').value = siswa.kelas_id;
    document.getElementById('edit_jenis_kelamin').value = siswa.jenis_kelamin;
    document.getElementById('edit_alamat').value = siswa.alamat;
    document.getElementById('edit_no_telp').value = siswa.no_telp;
    $('#editSiswaModal').modal('show');
}

function deleteSiswa(id, nama) {
    if (confirm('Apakah Anda yakin ingin menghapus data siswa "' + nama + '"?')) {
        document.getElementById('delete_id').value = id;
        document.getElementById('deleteSiswaForm').submit();
    }
}

// Initialize Select2
$(document).ready(function() {
    $('.select2').select2();
});

function filterSiswa() {
    var input, filter, table, tr, td, i, txtValue;
    input = document.getElementById("searchInput");
    filter = input.value.toLowerCase();
    table = document.querySelector(".table");
    tr = table.getElementsByTagName("tr");

    for (i = 1; i < tr.length; i++) { // Start from 1 to skip header row
        td = tr[i].getElementsByTagName("td")[2]; // Index 2 is for Nama Lengkap column
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

function viewSiswa(siswa) {
    document.getElementById('view_nis').textContent = siswa.nis;
    document.getElementById('view_nama_lengkap').textContent = siswa.nama_lengkap;
    document.getElementById('view_kelas').textContent = siswa.nama_kelas;
    document.getElementById('view_jenis_kelamin').textContent = siswa.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
    document.getElementById('view_alamat').textContent = siswa.alamat;
    document.getElementById('view_no_telp').textContent = siswa.no_telp;
    
    $('#viewSiswaModal').modal('show');
}
</script>

<?php include '../templates/footer.php'; ?> 