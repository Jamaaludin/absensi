<?php 
include '../templates/header.php';
include '../templates/sidebar.php';
require_once '../config/database.php';
checkRole(['wakasek']);

// Ambil guru_id dan mapel dari parameter URL
$guru_id = isset($_GET['guru_id']) ? trim($_GET['guru_id']) : null;
$mapel = isset($_GET['mapel']) ? trim($_GET['mapel']) : null;

// Validasi parameter
if (!$guru_id || !$mapel) {
    $_SESSION['error'] = "Parameter tidak valid";
    header("Location: guru.php");
    exit;
}

// Ambil data guru
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$guru_id]);
$guru = $stmt->fetch();

if (!$guru) {
    $_SESSION['error'] = "Data guru tidak ditemukan";
    header("Location: guru.php");
    exit;
}

// Ambil daftar kelas
$stmt = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas");
$kelas_list = $stmt->fetchAll();

// Ambil jadwal mengajar guru ini
$query = "SELECT jm.*, k.nama_kelas,
          CASE jm.hari 
            WHEN 1 THEN 'Senin'
            WHEN 2 THEN 'Selasa'
            WHEN 3 THEN 'Rabu'
            WHEN 4 THEN 'Kamis'
            WHEN 5 THEN 'Jumat'
            WHEN 6 THEN 'Sabtu'
          END as nama_hari
          FROM jam_mengajar jm
          JOIN kelas k ON jm.kelas_id = k.id
          WHERE jm.guru_id = ? AND jm.mata_pelajaran = ?
          ORDER BY jm.hari, jm.jam";
$stmt = $pdo->prepare($query);
$stmt->execute([$guru_id, $mapel]);
$jadwal_list = $stmt->fetchAll();

// Fungsi untuk cek bentrok jadwal
function cekBentrokJadwal($pdo, $kelas_id, $hari, $jam, $guru_id = null) {
    $sql = "SELECT jm.*, u.nama_lengkap as nama_guru 
            FROM jam_mengajar jm 
            JOIN users u ON jm.guru_id = u.id 
            WHERE jm.kelas_id = ? AND jm.hari = ? AND jm.jam = ?";
    $params = [$kelas_id, $hari, $jam];
    
    if ($guru_id) {
        $sql .= " AND jm.guru_id != ?";
        $params[] = $guru_id;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
}

// Proses CRUD
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        if (isset($_POST['action'])) {
            switch($_POST['action']) {
                case 'add':
                    // Cek jadwal bentrok
                    $bentrok = cekBentrokJadwal(
                        $pdo, 
                        $_POST['kelas_id'], 
                        $_POST['hari'], 
                        $_POST['jam'],
                        $guru_id
                    );
                    
                    if ($bentrok) {
                        throw new Exception(
                            "Jadwal bentrok dengan " . 
                            htmlspecialchars($bentrok['nama_guru']) . 
                            " pada hari " . $hari_list[$bentrok['hari']] . 
                            " jam ke-" . $bentrok['jam']
                        );
                    }
                    
                    $stmt = $pdo->prepare("INSERT INTO jam_mengajar (guru_id, kelas_id, hari, jam, mata_pelajaran) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$guru_id, $_POST['kelas_id'], $_POST['hari'], $_POST['jam'], $mapel]);
                    $_SESSION['success'] = "Jam mengajar berhasil ditambahkan!";
                    break;
                    
                case 'delete':
                    $stmt = $pdo->prepare("DELETE FROM jam_mengajar WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    $_SESSION['success'] = "Jam mengajar berhasil dihapus!";
                    break;
            }
            $pdo->commit();
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    
    header("Location: kelola_jam_mengajar.php?guru_id=" . $guru_id . "&mapel=" . urlencode($mapel));
    exit;
}
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Kelola Jam Mengajar</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="guru.php">Data Guru</a></li>
                        <li class="breadcrumb-item active">Kelola Jam Mengajar</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-check"></i> Sukses!</h5>
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-ban"></i> Error!</h5>
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Jadwal Mengajar: <?php echo htmlspecialchars($guru['nama_lengkap']); ?> 
                        (<?php echo htmlspecialchars($mapel); ?>)
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-tambah">
                            <i class="fas fa-plus"></i> Tambah Jadwal
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 50px">No</th>
                                    <th>Hari</th>
                                    <th>Jam Ke</th>
                                    <th>Kelas</th>
                                    <th style="width: 100px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                foreach($jadwal_list as $jadwal): 
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($jadwal['nama_hari']); ?></td>
                                    <td><?php echo htmlspecialchars($jadwal['jam']); ?></td>
                                    <td><?php echo htmlspecialchars($jadwal['nama_kelas']); ?></td>
                                    <td>
                                        <form action="" method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $jadwal['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" 
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
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

<!-- Modal Tambah -->
<div class="modal fade" id="modal-tambah">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Jadwal Mengajar</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Hari</label>
                        <select name="hari" class="form-control" required>
                            <option value="">Pilih Hari</option>
                            <option value="1">Senin</option>
                            <option value="2">Selasa</option>
                            <option value="3">Rabu</option>
                            <option value="4">Kamis</option>
                            <option value="5">Jumat</option>
                            <option value="6">Sabtu</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jam Ke</label>
                        <input type="number" name="jam" class="form-control" min="1" max="10" required>
                    </div>
                    <div class="form-group">
                        <label>Kelas</label>
                        <select name="kelas_id" class="form-control" required>
                            <option value="">Pilih Kelas</option>
                            <?php foreach($kelas_list as $kelas): ?>
                            <option value="<?php echo $kelas['id']; ?>">
                                <?php echo htmlspecialchars($kelas['nama_kelas']); ?>
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

<?php include '../templates/footer.php'; ?> 