<?php 
include '../templates/header.php';
include '../templates/sidebar.php';
require_once '../config/database.php';
checkRole(['wakasek']);

// Inisialisasi variabel
$success_msg = '';
$error_msg = '';

// Proses CRUD
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        if (isset($_POST['action'])) {
            // Tambah mata pelajaran
            if ($_POST['action'] == 'add') {
                $stmt = $pdo->prepare("INSERT INTO mata_pelajaran (kode, nama) VALUES (?, ?)");
                $stmt->execute([$_POST['kode'], $_POST['nama']]);
                $success_msg = "Mata pelajaran berhasil ditambahkan!";
            }
            // Edit mata pelajaran
            else if ($_POST['action'] == 'edit') {
                $stmt = $pdo->prepare("UPDATE mata_pelajaran SET kode = ?, nama = ? WHERE id = ?");
                $stmt->execute([$_POST['kode'], $_POST['nama'], $_POST['id']]);
                $success_msg = "Mata pelajaran berhasil diupdate!";
            }
            // Hapus mata pelajaran
            else if ($_POST['action'] == 'delete') {
                // Cek apakah mapel digunakan di tabel users
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE mata_pelajaran = ?");
                $stmt->execute([$_POST['id']]);
                if ($stmt->fetchColumn() > 0) {
                    throw new Exception("Mata pelajaran tidak dapat dihapus karena sedang digunakan!");
                }
                
                $stmt = $pdo->prepare("DELETE FROM mata_pelajaran WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $success_msg = "Mata pelajaran berhasil dihapus!";
            }
        }
        
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_msg = "Error: " . $e->getMessage();
    }
}

// Tambahkan ini di awal file untuk debugging
try {
    // Cek struktur tabel yang ada
    $stmt = $pdo->query("DESCRIBE mata_pelajaran");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    error_log("Columns in mata_pelajaran: " . print_r($columns, true));
} catch (PDOException $e) {
    error_log("Error checking table structure: " . $e->getMessage());
}

// Query untuk mengambil semua mata pelajaran
$query = "SELECT * FROM mata_pelajaran ORDER BY nama";
$stmt = $pdo->query($query);
$mapel_list = $stmt->fetchAll();

// Debug data
error_log("Data from mata_pelajaran: " . print_r($mapel_list, true));
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Data Mata Pelajaran</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
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
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addModal">
                        <i class="fas fa-plus"></i> Tambah Mata Pelajaran
                    </button>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr class="text-center">
                                <th width="5%">No</th>
                                <th width="15%">Kode</th>
                                <th>Nama Mata Pelajaran</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            foreach($mapel_list as $mapel): 
                            ?>
                            <tr>
                                <td class="text-center"><?php echo $no++; ?></td>
                                <td class="text-center"><?php echo htmlspecialchars($mapel['kode']); ?></td>
                                <td><?php echo htmlspecialchars($mapel['nama']); ?></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-warning btn-sm" onclick="editMapel(<?php echo htmlspecialchars(json_encode($mapel)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteMapel('<?php echo $mapel['id']; ?>', '<?php echo htmlspecialchars($mapel['nama']); ?>')">
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
<div class="modal fade" id="addModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Mata Pelajaran</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Kode Mata Pelajaran</label>
                        <input type="text" class="form-control" name="kode" required maxlength="10">
                    </div>
                    <div class="form-group">
                        <label>Nama Mata Pelajaran</label>
                        <input type="text" class="form-control" name="nama" required>
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

<!-- Modal Edit -->
<div class="modal fade" id="editModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Mata Pelajaran</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Kode Mata Pelajaran</label>
                        <input type="text" class="form-control" name="kode" id="edit_kode" required maxlength="10">
                    </div>
                    <div class="form-group">
                        <label>Nama Mata Pelajaran</label>
                        <input type="text" class="form-control" name="nama" id="edit_nama" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form untuk delete -->
<form id="deleteForm" action="" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
</form>

<script>
// Fungsi untuk edit mata pelajaran
function editMapel(mapel) {
    document.getElementById('edit_id').value = mapel.id;
    document.getElementById('edit_kode').value = mapel.kode;
    document.getElementById('edit_nama').value = mapel.nama;
    $('#editModal').modal('show');
}

// Fungsi untuk delete mata pelajaran
function deleteMapel(id, nama) {
    Swal.fire({
        title: 'Hapus Mata Pelajaran?',
        text: `Anda yakin ingin menghapus mata pelajaran "${nama}"?`,
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

<?php include '../templates/footer.php'; ?> 