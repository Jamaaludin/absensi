<?php 
include '../templates/header.php';
include '../templates/sidebar.php';
require_once '../config/database.php';
checkRole(['admin']);

// Inisialisasi variabel
$success_msg = '';
$error_msg = '';

// Tampilkan pesan dari session jika ada
if (isset($_SESSION['success_msg'])) {
    $success_msg = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}

if (isset($_SESSION['error_msg'])) {
    $error_msg = $_SESSION['error_msg'];
    unset($_SESSION['error_msg']);
}

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
                    $nip = $_POST['nip'];
                    $jenis_kelamin = $_POST['jenis_kelamin'];
                    $alamat = $_POST['alamat'];
                    $no_telp = $_POST['no_telp'];
                    $mata_pelajaran = isset($_POST['mata_pelajaran']) ? 
                        implode(', ', array_filter($_POST['mata_pelajaran'])) : '';
                    $status_wali_kelas = $_POST['status_wali_kelas'];
                    $kelas_wali = $_POST['kelas_wali'];
                    
                    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, nama_lengkap, nip, jenis_kelamin, alamat, no_telp, mata_pelajaran, is_wali_kelas) 
                                         VALUES (?, ?, 'guru', ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$username, $password, $nama_lengkap, $nip, $jenis_kelamin, $alamat, $no_telp, $mata_pelajaran, $status_wali_kelas]);
                    
                    if ($status_wali_kelas == '1' && !empty($kelas_wali)) {
                        // Cek apakah kelas sudah memiliki wali kelas
                        $stmt = $pdo->prepare("SELECT u.nama_lengkap FROM kelas k 
                                              JOIN users u ON k.wali_kelas_id = u.id 
                                              WHERE k.id = ?");
                        $stmt->execute([$kelas_wali]);
                        $existing_wali = $stmt->fetch();
                        
                        if ($existing_wali) {
                            throw new Exception("Kelas ini sudah memiliki wali kelas: " . $existing_wali['nama_lengkap']);
                        }
                        
                        // Update role menjadi wali_kelas
                        $stmt = $pdo->prepare("UPDATE users SET role = 'wali_kelas' WHERE id = ?");
                        $stmt->execute([$pdo->lastInsertId()]);
                        
                        // Update kelas dengan wali kelas baru
                        $stmt = $pdo->prepare("UPDATE kelas SET wali_kelas_id = ? WHERE id = ?");
                        $stmt->execute([$pdo->lastInsertId(), $kelas_wali]);
                    } else {
                        // Pastikan role adalah guru jika bukan wali kelas
                        $stmt = $pdo->prepare("UPDATE users SET role = 'guru' WHERE id = ?");
                        $stmt->execute([$pdo->lastInsertId()]);
                    }
                    
                    $success_msg = "Data guru berhasil ditambahkan!";
                    break;
                    
                case 'edit':
                    $id = $_POST['id'];
                    $nama_lengkap = $_POST['nama_lengkap'];
                    $nip = $_POST['nip'];
                    $jenis_kelamin = $_POST['jenis_kelamin'];
                    $alamat = $_POST['alamat'];
                    $no_telp = $_POST['no_telp'];
                    $mata_pelajaran = isset($_POST['mata_pelajaran']) ? 
                        implode(', ', array_filter($_POST['mata_pelajaran'])) : '';
                    $status_wali_kelas = $_POST['status_wali_kelas'];
                    $kelas_wali = $_POST['kelas_wali'];
                    
                    if (!empty($_POST['password'])) {
                        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET nama_lengkap=?, nip=?, jenis_kelamin=?, 
                                            alamat=?, no_telp=?, mata_pelajaran=?, password=?, is_wali_kelas=? WHERE id=?");
                        $stmt->execute([$nama_lengkap, $nip, $jenis_kelamin, $alamat, $no_telp, $mata_pelajaran, $password, $status_wali_kelas, $id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET nama_lengkap=?, nip=?, jenis_kelamin=?, 
                                            alamat=?, no_telp=?, mata_pelajaran=?, is_wali_kelas=? WHERE id=?");
                        $stmt->execute([$nama_lengkap, $nip, $jenis_kelamin, $alamat, $no_telp, $mata_pelajaran, $status_wali_kelas, $id]);
                    }
                    
                    if ($status_wali_kelas == '1' && !empty($kelas_wali)) {
                        // Cek apakah kelas sudah memiliki wali kelas yang berbeda
                        $stmt = $pdo->prepare("SELECT u.nama_lengkap, u.id FROM kelas k 
                                              JOIN users u ON k.wali_kelas_id = u.id 
                                              WHERE k.id = ? AND u.id != ?");
                        $stmt->execute([$kelas_wali, $id]);
                        $existing_wali = $stmt->fetch();
                        
                        if ($existing_wali) {
                            throw new Exception("Kelas ini sudah memiliki wali kelas: " . $existing_wali['nama_lengkap']);
                        }
                        
                        // Update role menjadi wali_kelas
                        $stmt = $pdo->prepare("UPDATE users SET role = 'wali_kelas' WHERE id = ?");
                        $stmt->execute([$id]);
                        
                        // Update kelas dengan wali kelas baru
                        $stmt = $pdo->prepare("UPDATE kelas SET wali_kelas_id = NULL WHERE wali_kelas_id = ?");
                        $stmt->execute([$id]);
                        $stmt = $pdo->prepare("UPDATE kelas SET wali_kelas_id = ? WHERE id = ?");
                        $stmt->execute([$id, $kelas_wali]);
                    } else {
                        // Kembalikan role menjadi guru jika bukan wali kelas
                        $stmt = $pdo->prepare("UPDATE users SET role = 'guru' WHERE id = ?");
                        $stmt->execute([$id]);
                        
                        // Hapus status wali kelas dari kelas yang ada
                        $stmt = $pdo->prepare("UPDATE kelas SET wali_kelas_id = NULL WHERE wali_kelas_id = ?");
                        $stmt->execute([$id]);
                    }
                    
                    $success_msg = "Data guru berhasil diupdate!";
                    break;
                    
                case 'delete':
                    $id = $_POST['id'];
                    // Cek apakah guru adalah wali kelas
                    $stmt = $pdo->prepare("SELECT id FROM kelas WHERE wali_kelas_id = ?");
                    $stmt->execute([$id]);
                    if ($stmt->rowCount() > 0) {
                        throw new Exception("Guru tidak dapat dihapus karena masih menjadi wali kelas!");
                    }
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$id]);
                    $success_msg = "Data guru berhasil dihapus!";
                    break;
            }
            $pdo->commit();
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_msg = "Error: " . $e->getMessage();
    }
}

// Ambil daftar guru
$stmt = $pdo->query("SELECT * FROM users WHERE role IN ('guru', 'wali_kelas') ORDER BY nama_lengkap");
$guru = $stmt->fetchAll();

// Ambil daftar kelas untuk dropdown
$stmt = $pdo->query("SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas");
$kelas_list = $stmt->fetchAll();

// Ambil daftar mata pelajaran untuk dropdown
$stmt = $pdo->query("SELECT id, kode, nama FROM mata_pelajaran ORDER BY nama");
$mata_pelajaran_list = $stmt->fetchAll();
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Manajemen Data Guru</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Manajemen Guru</li>
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
                                       placeholder="Cari berdasarkan nama lengkap..." onkeyup="filterGuru()">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="guru_export_csv.php" class="btn btn-info btn-sm">
                                <i class="fas fa-download"></i> Download Format CSV
                            </a>
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#importGuruModal">
                                <i class="fas fa-file-import"></i> Import CSV
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIP</th>
                                <th>Nama Lengkap</th>
                                <th>Mata Pelajaran</th>
                                <th>Jenis Kelamin</th>
                                <th>No. Telp</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($guru as $index => $g): 
                                // Cek status wali kelas
                                $stmt = $pdo->prepare("SELECT nama_kelas FROM kelas WHERE wali_kelas_id = ?");
                                $stmt->execute([$g['id']]);
                                $wali_kelas = $stmt->fetch();
                            ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($g['nip']); ?></td>
                                <td><?php echo htmlspecialchars($g['nama_lengkap']); ?></td>
                                <td><?php 
                                    // Tampilkan mata pelajaran dengan format yang lebih baik
                                    $mapel_array = explode(',', $g['mata_pelajaran']);
                                    $mapel_array = array_map('trim', $mapel_array);
                                    echo htmlspecialchars(implode(', ', array_filter($mapel_array))); 
                                ?></td>
                                <td><?php echo $g['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                                <td><?php echo htmlspecialchars($g['no_telp']); ?></td>
                                <td>
                                    <?php if ($wali_kelas): ?>
                                        <span class="badge badge-success">Wali Kelas</span>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($wali_kelas['nama_kelas']); ?></small>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Bukan Wali Kelas</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-info btn-sm" 
                                                onclick='editJadwal(<?php echo json_encode([
                                                    "id" => $g["id"],
                                                    "mata_pelajaran" => $g["mata_pelajaran"]
                                                ]); ?>)'>
                                            <i class="fas fa-clock"></i>
                                        </button>
                                        <button type="button" class="btn btn-warning btn-sm" onclick="editGuru(<?php echo htmlspecialchars(json_encode($g)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteGuru(<?php echo $g['id']; ?>, '<?php echo $g['nama_lengkap']; ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
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

<!-- Modal Tambah Guru -->
<div class="modal fade" id="addGuruModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Guru Baru</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label>NIP</label>
                        <input type="text" class="form-control" name="nip" required>
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
                    <!-- Container untuk field mata pelajaran dinamis -->
                    <div id="addMapelFieldsContainer">
                        <!-- Field mata pelajaran akan ditambahkan di sini -->
                    </div>
                    <div class="form-group">
                        <button type="button" class="btn btn-success btn-sm" onclick="addNewMapelFieldAdd()">
                            <i class="fas fa-plus"></i> Tambah Mata Pelajaran
                        </button>
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
                    <div class="form-group">
                        <label>Status Wali Kelas</label>
                        <select class="form-control" name="status_wali_kelas" required>
                            <option value="0">Bukan Wali Kelas</option>
                            <option value="1">Wali Kelas</option>
                        </select>
                    </div>
                    <div class="form-group" id="kelas_wali_group" style="display:none;">
                        <label>Kelas yang Diampu</label>
                        <select class="form-control" name="kelas_wali" id="kelas_wali">
                            <option value="">Pilih Kelas</option>
                            <?php 
                            // Ambil hanya kelas yang belum memiliki wali kelas
                            $stmt = $pdo->query("SELECT id, nama_kelas FROM kelas WHERE wali_kelas_id IS NULL ORDER BY nama_kelas");
                            $available_kelas = $stmt->fetchAll();
                            foreach($available_kelas as $k): 
                            ?>
                            <option value="<?php echo $k['id']; ?>"><?php echo htmlspecialchars($k['nama_kelas']); ?></option>
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

<!-- Modal Edit Guru -->
<div class="modal fade" id="editGuruModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Guru</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label>NIP</label>
                        <input type="text" class="form-control" name="nip" id="edit_nip" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama_lengkap" id="edit_nama_lengkap" required>
                    </div>
                    <!-- Container untuk field mata pelajaran dinamis -->
                    <div id="mapelFieldsContainer">
                        <!-- Field mata pelajaran akan ditambahkan di sini -->
                    </div>
                    <div class="form-group">
                        <button type="button" class="btn btn-success btn-sm" onclick="addNewMapelField()">
                            <i class="fas fa-plus"></i> Tambah Mata Pelajaran
                        </button>
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
                    <div class="form-group">
                        <label>Status Wali Kelas</label>
                        <select class="form-control" name="status_wali_kelas" id="edit_status_wali_kelas" required>
                            <option value="0">Bukan Wali Kelas</option>
                            <option value="1">Wali Kelas</option>
                        </select>
                    </div>
                    <div class="form-group" style="display:none;">
                        <label>Kelas yang Diampu</label>
                        <select class="form-control" name="kelas_wali" id="edit_kelas_wali">
                            <option value="">Pilih Kelas</option>
                            <?php 
                            // Ambil hanya kelas yang belum memiliki wali kelas
                            $stmt = $pdo->query("SELECT id, nama_kelas FROM kelas WHERE wali_kelas_id IS NULL ORDER BY nama_kelas");
                            $available_kelas = $stmt->fetchAll();
                            foreach($available_kelas as $k): 
                            ?>
                            <option value="<?php echo $k['id']; ?>"><?php echo htmlspecialchars($k['nama_kelas']); ?></option>
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

<!-- Modal View Guru -->
<div class="modal fade" id="viewGuruModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Detail Guru</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 150px;">NIP</th>
                        <td id="view_nip"></td>
                    </tr>
                    <tr>
                        <th>Nama Lengkap</th>
                        <td id="view_nama_lengkap"></td>
                    </tr>
                    <tr>
                        <th>Mata Pelajaran</th>
                        <td id="view_mata_pelajaran"></td>
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

<!-- Form Hapus Guru -->
<form id="deleteGuruForm" action="" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
</form>

<!-- Tambahkan Modal Jam Mengajar sebelum script -->
<div class="modal fade" id="jamMengajarModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Jam Mengajar: <span id="nama_guru_mengajar"></span></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formJamMengajar" action="" method="POST">
                    <input type="hidden" name="action" value="add_jam">
                    <input type="hidden" name="guru_id" id="guru_id_mengajar">
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Kelas</label>
                                <select class="form-control select2" name="kelas_id" required>
                                    <option value="">Pilih Kelas</option>
                                    <?php foreach($kelas_list as $k): ?>
                                    <option value="<?php echo $k['id']; ?>"><?php echo htmlspecialchars($k['nama_kelas']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Hari</label>
                                <select class="form-control" name="hari" required>
                                    <option value="Senin">Senin</option>
                                    <option value="Selasa">Selasa</option>
                                    <option value="Rabu">Rabu</option>
                                    <option value="Kamis">Kamis</option>
                                    <option value="Jumat">Jumat</option>
                                    <option value="Sabtu">Sabtu</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Jam Ke-</label>
                                <select class="form-control" name="jam" required>
                                    <?php for($i=1; $i<=8; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Tambah Jam</button>
                </form>

                <hr>

                <div class="table-responsive">
                    <table class="table table-bordered" id="tabelJamMengajar">
                        <thead>
                            <tr>
                                <th>Hari</th>
                                <?php for($i=1; $i<=8; $i++): ?>
                                <th>Jam <?php echo $i; ?></th>
                                <?php endfor; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                            foreach($hari as $h): 
                            ?>
                            <tr>
                                <td><strong><?php echo $h; ?></strong></td>
                                <?php for($i=1; $i<=8; $i++): ?>
                                <td id="jadwal_<?php echo $h; ?>_<?php echo $i; ?>">-</td>
                                <?php endfor; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Import -->
<div class="modal fade" id="importGuruModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Import Data Guru</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="import_guru.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label>File CSV</label>
                        <input type="file" class="form-control" name="csv_file" accept=".csv" required>
                    </div>
                    <small class="form-text text-muted">
                        <strong>Format File:</strong><br>
                        - NIP: Nomor Induk Pegawai<br>
                        - Username: Username untuk login<br>
                        - Nama Lengkap: Nama lengkap guru<br>
                        - Jenis Kelamin: L/P<br>
                        - No. Telp: Nomor telepon<br>
                        - Mata Pelajaran: Bisa multiple, pisahkan dengan koma (contoh: "Matematika,Fisika")<br>
                        - Hari: Nama hari (Senin/Selasa/Rabu/Kamis/Jumat/Sabtu), bisa multiple<br>
                        - Kelas: Sesuai jadwal mengajar, bisa multiple (contoh: "X IPA 1,X IPA 2")<br>
                        - Jam Pelajaran: Format "jam_awal-jam_akhir", bisa multiple (contoh: "1-2,3-4")<br>
                        <strong>Catatan:</strong><br>
                        - Password default sama dengan username<br>
                        - Jumlah data Hari, Kelas, dan Jam Pelajaran harus sama<br>
                        - Format hari harus sesuai (Senin=1, Selasa=2, Rabu=3, Kamis=4, Jumat=5, Sabtu=6)<br>
                        - Download format CSV untuk melihat contoh
                    </small>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Pilih Mata Pelajaran -->
<div class="modal fade" id="pilihMapelModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Pilih Mata Pelajaran</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="selected_guru_id">
                <div class="form-group">
                    <label>Mata Pelajaran</label>
                    <select class="form-control" id="mapel_select">
                        <option value="">Pilih Mata Pelajaran</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="goToKelolaJadwal()">Lanjutkan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Mata Pelajaran -->
<div class="modal fade" id="editMapelModal" tabindex="-1" role="dialog" aria-labelledby="editMapelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMapelModalLabel">Edit Mata Pelajaran</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formEditMapel">
                    <input type="hidden" name="guru_id" id="editGuruId">
                    <div class="form-group">
                        <label>Daftar Mata Pelajaran</label>
                        <div id="mapelContainer">
                            <!-- Container untuk daftar mapel -->
                        </div>
                        <button type="button" class="btn btn-success btn-sm mt-2" onclick="addMapelField()">
                            <i class="fas fa-plus"></i> Tambah Mata Pelajaran
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="saveMapel()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Tambahkan script pencarian -->
<script>
function viewGuru(guru) {
    document.getElementById('view_nip').textContent = guru.nip || '-';
    document.getElementById('view_nama_lengkap').textContent = guru.nama_lengkap;
    document.getElementById('view_mata_pelajaran').textContent = guru.mata_pelajaran || '-';
    document.getElementById('view_jenis_kelamin').textContent = guru.jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan';
    document.getElementById('view_alamat').textContent = guru.alamat || '-';
    document.getElementById('view_no_telp').textContent = guru.no_telp || '-';
    $('#viewGuruModal').modal('show');
}

function addNewMapelField(value = '') {
    const container = document.getElementById('mapelFieldsContainer');
    const fieldCount = container.children.length;
    
    const div = document.createElement('div');
    div.className = 'form-group';
    div.innerHTML = `
        <div class="input-group">
            <select class="form-control" name="mata_pelajaran[]" required>
                <option value="">Pilih Mata Pelajaran</option>
                <?php foreach($mata_pelajaran_list as $mp): ?>
                <option value="<?php echo htmlspecialchars($mp['nama']); ?>" ${value === '<?php echo htmlspecialchars($mp['nama']); ?>' ? 'selected' : ''}>
                    <?php echo htmlspecialchars($mp['nama']); ?>
                </option>
                <?php endforeach; ?>
            </select>
            <div class="input-group-append">
                <button type="button" class="btn btn-danger" onclick="removeMapelField(this)">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(div);
}

function removeMapelField(button) {
    button.closest('.form-group').remove();
}

function editGuru(guru) {
    document.getElementById('edit_id').value = guru.id;
    document.getElementById('edit_nip').value = guru.nip;
    document.getElementById('edit_nama_lengkap').value = guru.nama_lengkap;
    // Reset dan populate mata pelajaran
    const mapelContainer = document.getElementById('mapelFieldsContainer');
    mapelContainer.innerHTML = ''; // Clear existing fields
    
    if (guru.mata_pelajaran) {
        const mapelArray = guru.mata_pelajaran.split(',').map(m => m.trim());
        mapelArray.forEach(mapel => {
            if (mapel) addNewMapelField(mapel);
        });
    } else {
        addNewMapelField(); // Add one empty field if no mata pelajaran
    }
    
    document.getElementById('edit_jenis_kelamin').value = guru.jenis_kelamin;
    document.getElementById('edit_no_telp').value = guru.no_telp;
    $('#editGuruModal').modal('show');
}

function deleteGuru(id, nama) {
    if (confirm('Apakah Anda yakin ingin menghapus data guru "' + nama + '"?')) {
        document.getElementById('delete_id').value = id;
        document.getElementById('deleteGuruForm').submit();
    }
}

function jamMengajar(id, nama) {
    document.getElementById('guru_id_mengajar').value = id;
    document.getElementById('nama_guru_mengajar').textContent = nama;
    
    // Reset tabel
    const cells = document.querySelectorAll('[id^="jadwal_"]');
    cells.forEach(cell => cell.textContent = '-');
    
    // Ambil data jam mengajar
    fetch(`get_jam_mengajar.php?guru_id=${id}`)
        .then(response => response.json())
        .then(data => {
            data.forEach(jadwal => {
                const cell = document.getElementById(`jadwal_${jadwal.hari}_${jadwal.jam}`);
                cell.innerHTML = `${jadwal.nama_kelas} <button onclick="hapusJam(${jadwal.id})" class="btn btn-danger btn-xs ml-1"><i class="fas fa-times"></i></button>`;
            });
        });
    
    $('#jamMengajarModal').modal('show');
}

function hapusJam(id) {
    if(confirm('Apakah Anda yakin ingin menghapus jam mengajar ini?')) {
        fetch('hapus_jam_mengajar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                const guruId = document.getElementById('guru_id_mengajar').value;
                jamMengajar(guruId, document.getElementById('nama_guru_mengajar').textContent);
            } else {
                alert('Gagal menghapus jam mengajar');
            }
        });
    }
}

// Tambahkan event listener untuk form jam mengajar
document.getElementById('formJamMengajar').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('tambah_jam_mengajar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            const guruId = document.getElementById('guru_id_mengajar').value;
            jamMengajar(guruId, document.getElementById('nama_guru_mengajar').textContent);
            // Tampilkan pesan sukses
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message
            });
        } else {
            // Tampilkan pesan error
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: data.message
            });
        }
    });
});

$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4'
    });

    // Handler untuk form tambah guru
    $('select[name="status_wali_kelas"]').change(function() {
        const kelasField = $('#kelas_wali_group');
        if ($(this).val() == '1') {
            kelasField.show();
            $('#kelas_wali').prop('required', true);
        } else {
            kelasField.hide();
            $('#kelas_wali').prop('required', false);
        }
    });

    // Handler untuk form edit guru
    $('#edit_status_wali_kelas').change(function() {
        const kelasField = $('#edit_kelas_wali');
        if ($(this).val() == '1') {
            kelasField.closest('.form-group').show();
            kelasField.prop('required', true);
        } else {
            kelasField.closest('.form-group').hide();
            kelasField.prop('required', false);
        }
    });
});

function filterGuru() {
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

function showJadwalModal(guru_id, mapel_list) {
    // Set guru_id ke hidden input
    document.getElementById('selected_guru_id').value = guru_id;
    
    // Populate mapel select
    const mapelSelect = document.getElementById('mapel_select');
    mapelSelect.innerHTML = '<option value="">Pilih Mata Pelajaran</option>';
    
    // Handle jika mapel_list adalah string atau array
    let mapels = [];
    if (typeof mapel_list === 'string') {
        mapels = mapel_list.split(',').map(m => m.trim());
    } else if (Array.isArray(mapel_list)) {
        mapels = mapel_list;
    }
    
    mapels.forEach(mapel => {
        if (mapel) {
            const option = document.createElement('option');
            option.value = mapel;
            option.textContent = mapel;
            mapelSelect.appendChild(option);
        }
    });
    
    // Show modal
    $('#pilihMapelModal').modal('show');
}

function editJadwal(guru) {
    console.log('editJadwal called with:', guru); // Untuk debugging
    showJadwalModal(guru.id, guru.mata_pelajaran);
}

function goToKelolaJadwal() {
    const guru_id = document.getElementById('selected_guru_id').value;
    const mapel = document.getElementById('mapel_select').value;
    
    if (!mapel) {
        alert('Silakan pilih mata pelajaran terlebih dahulu!');
        return;
    }
    
    // Redirect ke halaman kelola jadwal dengan parameter
    window.location.href = '../admin/kelola_jam_mengajar.php?guru_id=' + guru_id + '&mapel=' + encodeURIComponent(mapel);
}

let mapelList = [];

function editMapel(guruId) {
    // Reset container
    $('#mapelContainer').empty();
    $('#editGuruId').val(guruId);
    
    // Ambil data mapel guru
    $.ajax({
        url: 'get_mapel_guru.php',
        type: 'GET',
        data: { guru_id: guruId },
        success: function(response) {
            mapelList = response;
            // Tampilkan setiap mapel
            mapelList.forEach((mapel, index) => {
                addMapelField(mapel);
            });
            $('#editMapelModal').modal('show');
        },
        error: function(xhr, status, error) {
            alert('Error: ' + error);
        }
    });
}

function addMapelField(mapel = '') {
    const index = $('#mapelContainer').children().length;
    const html = `
        <div class="input-group mb-2">
            <input type="text" class="form-control" name="mapel[]" value="${mapel}" placeholder="Nama mata pelajaran">
            <div class="input-group-append">
                <button type="button" class="btn btn-danger" onclick="removeMapelField(this)">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
    `;
    $('#mapelContainer').append(html);
}

function removeMapelField(btn) {
    $(btn).closest('.input-group').remove();
}

function saveMapel() {
    const guruId = $('#editGuruId').val();
    const mapel = $('input[name="mapel[]"]').map(function() {
        return $(this).val();
    }).get();

    $.ajax({
        url: 'update_mapel_guru.php',
        type: 'POST',
        data: {
            guru_id: guruId,
            mapel: mapel
        },
        success: function(response) {
            if(response.success) {
                alert('Mata pelajaran berhasil diupdate!');
                $('#editMapelModal').modal('hide');
                // Refresh tabel atau bagian yang menampilkan mapel
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            alert('Error: ' + error);
        }
    });
}

function addNewMapelFieldAdd(value = '') {
    const container = document.getElementById('addMapelFieldsContainer');
    const fieldCount = container.children.length;
    
    const div = document.createElement('div');
    div.className = 'form-group';
    div.innerHTML = `
        <div class="input-group">
            <select class="form-control" name="mata_pelajaran[]" required>
                <option value="">Pilih Mata Pelajaran</option>
                <?php foreach($mata_pelajaran_list as $mp): ?>
                <option value="<?php echo htmlspecialchars($mp['nama']); ?>" ${value === '<?php echo htmlspecialchars($mp['nama']); ?>' ? 'selected' : ''}>
                    <?php echo htmlspecialchars($mp['nama']); ?>
                </option>
                <?php endforeach; ?>
            </select>
            <div class="input-group-append">
                <button type="button" class="btn btn-danger" onclick="removeMapelField(this)">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(div);
}

// Tambahkan satu field mata pelajaran saat modal tambah guru dibuka
$('#addGuruModal').on('show.bs.modal', function() {
    const container = document.getElementById('addMapelFieldsContainer');
    container.innerHTML = ''; // Reset container
    addNewMapelFieldAdd(); // Tambah satu field kosong
});
</script>

<?php include '../templates/footer.php'; ?> 