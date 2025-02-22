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
                    $nama_kelas = $_POST['nama_kelas'];
                    $wali_kelas_id = $_POST['wali_kelas_id'];
                    $tahun_ajaran = $_POST['tahun_ajaran'];
                    
                    $stmt = $pdo->prepare("INSERT INTO kelas (nama_kelas, wali_kelas_id, tahun_ajaran) VALUES (?, ?, ?)");
                    $stmt->execute([$nama_kelas, $wali_kelas_id, $tahun_ajaran]);
                    $success_msg = "Kelas berhasil ditambahkan!";
                    break;
                    
                case 'edit':
                    $id = $_POST['id'];
                    $nama_kelas = $_POST['nama_kelas'];
                    $wali_kelas_id = $_POST['wali_kelas_id'];
                    $tahun_ajaran = $_POST['tahun_ajaran'];
                    
                    $stmt = $pdo->prepare("UPDATE kelas SET nama_kelas = ?, wali_kelas_id = ?, tahun_ajaran = ? WHERE id = ?");
                    $stmt->execute([$nama_kelas, $wali_kelas_id, $tahun_ajaran, $id]);
                    $success_msg = "Data kelas berhasil diupdate!";
                    break;
                    
                case 'delete':
                    $id = $_POST['id'];
                    $stmt = $pdo->prepare("DELETE FROM kelas WHERE id = ?");
                    $stmt->execute([$id]);
                    $success_msg = "Kelas berhasil dihapus!";
                    break;
            }
            $pdo->commit();
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_msg = "Error: " . $e->getMessage();
    }
}

// Ambil daftar guru untuk wali kelas
$stmt = $pdo->query("SELECT id, nama_lengkap FROM users WHERE role = 'guru' ORDER BY nama_lengkap");
$guru_list = $stmt->fetchAll();

// Ambil daftar kelas
$stmt = $pdo->query("SELECT k.*, u.nama_lengkap as wali_kelas 
                     FROM kelas k 
                     LEFT JOIN users u ON k.wali_kelas_id = u.id 
                     ORDER BY k.nama_kelas");
$kelas = $stmt->fetchAll();
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Manajemen Kelas</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Manajemen Kelas</li>
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
                    <h3 class="card-title">Daftar Kelas</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-tambah">
                            <i class="fas fa-plus"></i> Tambah Kelas
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Kelas</th>
                                <th>Wali Kelas</th>
                                <th>Tahun Ajaran</th>
                                <th>Jumlah Siswa</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($kelas as $index => $k): 
                                // Hitung jumlah siswa per kelas
                                $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE kelas_id = ? AND role = 'siswa'");
                                $stmt->execute([$k['id']]);
                                $jumlah_siswa = $stmt->fetch()['total'];
                            ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($k['nama_kelas']); ?></td>
                                <td><?php echo htmlspecialchars($k['wali_kelas'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($k['tahun_ajaran']); ?></td>
                                <td><?php echo $jumlah_siswa; ?> siswa</td>
                                <td>
                                    <button type="button" class="btn btn-warning btn-sm" onclick="editKelas(<?php echo htmlspecialchars(json_encode($k)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteKelas(<?php echo $k['id']; ?>, '<?php echo $k['nama_kelas']; ?>')">
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

<!-- Modal Tambah -->
<div class="modal fade" id="modal-tambah">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Kelas Baru</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label>Nama Kelas</label>
                        <input type="text" class="form-control" name="nama_kelas" required>
                    </div>
                    <div class="form-group">
                        <label>Tahun Ajaran</label>
                        <select class="form-control" name="tahun_ajaran" required>
                            <?php for($i = date('Y'); $i >= date('Y')-5; $i--): ?>
                                <option value="<?php echo $i; ?>/<?php echo $i+1; ?>">
                                    <?php echo $i; ?>/<?php echo $i+1; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group" style="display: none;">
                        <label>Wali Kelas</label>
                        <select class="form-control select2" name="wali_kelas_id">
                            <option value="">Pilih Wali Kelas</option>
                            <?php foreach($guru_list as $g): ?>
                                <option value="<?php echo $g['id']; ?>">
                                    <?php echo htmlspecialchars($g['nama_lengkap']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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

<!-- Modal Edit -->
<div class="modal fade" id="modal-edit">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Kelas</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label>Nama Kelas</label>
                        <input type="text" class="form-control" name="nama_kelas" id="edit_nama_kelas" required>
                    </div>
                    <div class="form-group">
                        <label>Tahun Ajaran</label>
                        <select class="form-control" name="tahun_ajaran" id="edit_tahun_ajaran" required>
                            <?php for($i = date('Y'); $i >= date('Y')-5; $i--): ?>
                                <option value="<?php echo $i; ?>/<?php echo $i+1; ?>">
                                    <?php echo $i; ?>/<?php echo $i+1; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group" style="display: none;">
                        <label>Wali Kelas</label>
                        <select class="form-control select2" name="wali_kelas_id" id="edit_wali_kelas_id">
                            <option value="">Pilih Wali Kelas</option>
                            <?php foreach($guru_list as $g): ?>
                                <option value="<?php echo $g['id']; ?>">
                                    <?php echo htmlspecialchars($g['nama_lengkap']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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

<!-- Form Hapus Kelas -->
<form id="deleteKelasForm" action="" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
</form>

<script>
function editKelas(kelas) {
    document.getElementById('edit_id').value = kelas.id;
    document.getElementById('edit_nama_kelas').value = kelas.nama_kelas;
    document.getElementById('edit_wali_kelas_id').value = kelas.wali_kelas_id;
    document.getElementById('edit_tahun_ajaran').value = kelas.tahun_ajaran;
    $('#modal-edit').modal('show');
}

function deleteKelas(id, nama) {
    if (confirm('Apakah Anda yakin ingin menghapus kelas "' + nama + '"?')) {
        document.getElementById('delete_id').value = id;
        document.getElementById('deleteKelasForm').submit();
    }
}

// Initialize Select2
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap4'
    });
});
</script>

<?php include '../templates/footer.php'; ?> 