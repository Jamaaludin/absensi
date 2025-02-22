<?php 
include '../templates/header.php';
include '../templates/sidebar.php';
require_once '../config/database.php';
checkRole(['wakasek']);

// Ambil daftar kelas
$query = "SELECT * FROM kelas ORDER BY nama_kelas";
$stmt = $pdo->query($query);
$kelas_list = $stmt->fetchAll();

// Proses tambah siswa
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Validasi input
        if (empty($_POST['username']) || empty($_POST['nama_lengkap']) || empty($_POST['kelas_id'])) {
            throw new Exception('Semua field harus diisi!');
        }

        // Cek NIS unik
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$_POST['username']]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('NIS sudah terdaftar!');
        }

        // Hash password (default: NIS)
        $password = password_hash($_POST['username'], PASSWORD_DEFAULT);
        
        // Upload foto jika ada
        $foto_name = null;
        if (!empty($_FILES['foto']['name'])) {
            $foto = $_FILES['foto'];
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
            $max_size = 2 * 1024 * 1024; // 2MB
            
            if (!in_array($foto['type'], $allowed_types)) {
                throw new Exception('Tipe file tidak didukung. Gunakan JPG atau PNG.');
            }
            
            if ($foto['size'] > $max_size) {
                throw new Exception('Ukuran file terlalu besar. Maksimal 2MB.');
            }
            
            $ext = pathinfo($foto['name'], PATHINFO_EXTENSION);
            $foto_name = 'siswa_' . $_POST['username'] . '_' . time() . '.' . $ext;
            
            $upload_dir = '../assets/img/profile/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            if (!move_uploaded_file($foto['tmp_name'], $upload_dir . $foto_name)) {
                throw new Exception('Gagal mengupload foto.');
            }
        }

        // Insert data siswa
        $stmt = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, jenis_kelamin, kelas_id, role, foto) VALUES (?, ?, ?, ?, ?, 'siswa', ?)");
        $stmt->execute([
            $_POST['username'],
            $password,
            $_POST['nama_lengkap'],
            $_POST['jenis_kelamin'],
            $_POST['kelas_id'],
            $foto_name
        ]);

        $pdo->commit();
        $_SESSION['success'] = "Data siswa berhasil ditambahkan!";
        header("Location: siswa.php");
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        // Hapus foto jika ada error
        if (isset($foto_name) && file_exists('../assets/img/profile/' . $foto_name)) {
            unlink('../assets/img/profile/' . $foto_name);
        }
        $error_msg = $e->getMessage();
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
                    <h1 class="m-0">Tambah Siswa</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="siswa.php">Data Siswa</a></li>
                        <li class="breadcrumb-item active">Tambah Siswa</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <?php if (isset($error_msg)): ?>
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
                            <h3 class="card-title">Form Tambah Siswa</h3>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="../format/format_siswa.csv" class="btn btn-info">
                                <i class="fas fa-download"></i> Download Format CSV
                            </a>
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#importModal">
                                <i class="fas fa-file-import"></i> Import CSV
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>NIS</label>
                            <input type="text" class="form-control" name="username" required>
                            <small class="form-text text-muted">NIS akan digunakan sebagai username dan password default</small>
                        </div>
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" class="form-control" name="nama_lengkap" required>
                        </div>
                        <div class="form-group">
                            <label>Jenis Kelamin</label>
                            <select class="form-control" name="jenis_kelamin" required>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Kelas</label>
                            <select class="form-control" name="kelas_id" required>
                                <option value="">Pilih Kelas</option>
                                <?php foreach($kelas_list as $kelas): ?>
                                    <option value="<?php echo $kelas['id']; ?>">
                                        <?php echo htmlspecialchars($kelas['nama_kelas']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Foto</label>
                            <input type="file" class="form-control-file" name="foto" accept="image/jpeg,image/jpg,image/png">
                            <small class="form-text text-muted">
                                Upload foto siswa (Format: JPG/JPEG/PNG, Max: 2MB)
                            </small>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="siswa.php" class="btn btn-secondary">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Tambahkan modal import di akhir content-wrapper -->
<div class="modal fade" id="importModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Import Data Siswa</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="import_siswa.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label>File CSV</label>
                        <input type="file" class="form-control-file" name="csv_file" accept=".csv" required>
                        <small class="form-text text-muted">Upload file CSV sesuai format yang telah disediakan</small>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../templates/footer.php'; ?> 