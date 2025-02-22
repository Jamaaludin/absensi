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
                    $kode = $_POST['kode'];
                    $nama = $_POST['nama'];
                    
                    $stmt = $pdo->prepare("INSERT INTO mata_pelajaran (kode, nama) VALUES (?, ?)");
                    $stmt->execute([$kode, $nama]);
                    $success_msg = "Mata pelajaran berhasil ditambahkan!";
                    break;
                    
                case 'edit':
                    $id = $_POST['id'];
                    $kode = $_POST['kode'];
                    $nama = $_POST['nama'];
                    
                    $stmt = $pdo->prepare("UPDATE mata_pelajaran SET kode = ?, nama = ? WHERE id = ?");
                    $stmt->execute([$kode, $nama, $id]);
                    $success_msg = "Mata pelajaran berhasil diupdate!";
                    break;
                    
                case 'delete':
                    $id = $_POST['id'];
                    $stmt = $pdo->prepare("DELETE FROM mata_pelajaran WHERE id = ?");
                    $stmt->execute([$id]);
                    $success_msg = "Mata pelajaran berhasil dihapus!";
                    break;
            }
            $pdo->commit();
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_msg = "Error: " . $e->getMessage();
    }
}

// Ambil daftar mata pelajaran
$stmt = $pdo->query("SELECT * FROM mata_pelajaran ORDER BY nama");
$mata_pelajaran = $stmt->fetchAll();
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Manajemen Mata Pelajaran</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item">Manajemen Guru</li>
                        <li class="breadcrumb-item active">Mata Pelajaran</li>
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
                    <h3 class="card-title">Daftar Mata Pelajaran</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addMapelModal">
                            <i class="fas fa-plus"></i> Tambah Mata Pelajaran
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Nama Mata Pelajaran</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($mata_pelajaran as $index => $mp): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($mp['kode']); ?></td>
                                <td><?php echo htmlspecialchars($mp['nama']); ?></td>
                                <td>
                                    <button type="button" class="btn btn-warning btn-sm" onclick='editMapel(<?php echo json_encode($mp); ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteMapel(<?php echo $mp['id']; ?>, '<?php echo $mp['nama']; ?>')">
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

<!-- Modal Tambah Mata Pelajaran -->
<div class="modal fade" id="addMapelModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Mata Pelajaran</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label>Kode Mata Pelajaran</label>
                        <input type="text" class="form-control" name="kode" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Mata Pelajaran</label>
                        <input type="text" class="form-control" name="nama" required>
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

<!-- Modal Edit Mata Pelajaran -->
<div class="modal fade" id="editMapelModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Mata Pelajaran</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label>Kode Mata Pelajaran</label>
                        <input type="text" class="form-control" name="kode" id="edit_kode" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Mata Pelajaran</label>
                        <input type="text" class="form-control" name="nama" id="edit_nama" required>
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

<!-- Form Hapus Mata Pelajaran -->
<form id="deleteMapelForm" action="" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
</form>

<script>
function editMapel(mapel) {
    document.getElementById('edit_id').value = mapel.id;
    document.getElementById('edit_kode').value = mapel.kode;
    document.getElementById('edit_nama').value = mapel.nama;
    $('#editMapelModal').modal('show');
}

function deleteMapel(id, nama) {
    if (confirm('Apakah Anda yakin ingin menghapus mata pelajaran "' + nama + '"?')) {
        document.getElementById('delete_id').value = id;
        document.getElementById('deleteMapelForm').submit();
    }
}
</script>

<?php include '../templates/footer.php'; ?> 