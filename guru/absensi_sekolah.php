<?php 
require_once '../config/init.php';
checkRole(['guru', 'wali_kelas']);

include '../templates/header.php';
include '../templates/sidebar.php';
require_once '../config/database.php';

// Inisialisasi variabel
$success_msg = '';
$error_msg = '';

// Proses CRUD Absensi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        if (isset($_POST['action']) && $_POST['action'] == 'absensi') {
            $tanggal = $_POST['tanggal'];
            $siswa_status = $_POST['status']; // Array of user_id => status
            
            foreach ($siswa_status as $user_id => $status) {
                // Cek apakah sudah ada absensi untuk siswa di tanggal tersebut
                $stmt = $pdo->prepare("SELECT id FROM absensi WHERE user_id = ? AND tanggal = ?");
                $stmt->execute([$user_id, $tanggal]);
                $existing = $stmt->fetch();
                
                if ($existing) {
                    // Update absensi yang sudah ada
                    $stmt = $pdo->prepare("UPDATE absensi SET status = ? WHERE id = ?");
                    $stmt->execute([$status, $existing['id']]);
                } else {
                    // Insert absensi baru
                    $stmt = $pdo->prepare("INSERT INTO absensi (user_id, tanggal, status) VALUES (?, ?, ?)");
                    $stmt->execute([$user_id, $tanggal, $status]);
                }
            }
            
            $pdo->commit();
            $success_msg = "Absensi berhasil disimpan!";
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_msg = "Error: " . $e->getMessage();
    }
}

// Ambil daftar kelas yang diajar oleh guru
$stmt = $pdo->prepare("SELECT DISTINCT k.id, k.nama_kelas 
                       FROM jam_mengajar jm 
                       JOIN kelas k ON jm.kelas_id = k.id 
                       WHERE jm.guru_id = ? 
                       ORDER BY k.nama_kelas");
$stmt->execute([$_SESSION['user_id']]);
$kelas_list = $stmt->fetchAll();

// Query default untuk menampilkan semua siswa dan absensi hari ini
$today = date('Y-m-d');
$where_clause = "";
$params = [$_SESSION['user_id']];

if (isset($_GET['kelas_id']) && $_GET['kelas_id'] != '') {
    $where_clause = "AND k.id = ?";
    $params[] = $_GET['kelas_id'];
}

// Query untuk mengambil siswa dari kelas yang diajar
$query = "SELECT DISTINCT u.id, u.nis, u.nama_lengkap, k.nama_kelas, 
          COALESCE(a.status, 'hadir') as status
          FROM users u 
          JOIN kelas k ON u.kelas_id = k.id
          JOIN jam_mengajar jm ON k.id = jm.kelas_id
          LEFT JOIN absensi a ON u.id = a.user_id AND a.tanggal = ?
          WHERE u.role = 'siswa' 
          AND jm.guru_id = ? 
          $where_clause
          ORDER BY k.nama_kelas, u.nama_lengkap";

$stmt = $pdo->prepare($query);
array_unshift($params, $today); // Tambahkan tanggal di awal array params
$stmt->execute($params);
$siswa_list = $stmt->fetchAll();
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Absensi Sekolah</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Absensi Sekolah</li>
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
                            <div class="form-group">
                                <label>Filter Kelas:</label>
                                <select class="form-control" id="filterKelas" onchange="filterSiswa()">
                                    <option value="">Semua Kelas</option>
                                    <?php foreach($kelas_list as $k): ?>
                                        <option value="<?php echo $k['id']; ?>" <?php echo (isset($_GET['kelas_id']) && $_GET['kelas_id'] == $k['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($k['nama_kelas']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-absensi">
                                <i class="fas fa-plus"></i> Input Absensi
                            </button>
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
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($siswa_list as $index => $s): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($s['nis']); ?></td>
                                <td><?php echo htmlspecialchars($s['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars($s['nama_kelas']); ?></td>
                                <td>
                                    <?php
                                    $badge_class = 'success';
                                    $status_text = ucfirst($s['status']);
                                    switch($s['status']) {
                                        case 'izin':
                                            $badge_class = 'secondary';
                                            break;
                                        case 'sakit':
                                            $badge_class = 'warning';
                                            break;
                                        case 'alpha':
                                            $badge_class = 'danger';
                                            break;
                                    }
                                    ?>
                                    <span class="badge badge-<?php echo $badge_class; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
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

<!-- Modal Absensi -->
<div class="modal fade" id="modal-absensi">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Input Absensi</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="absensi">
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" class="form-control" name="tanggal" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($siswa_list as $s): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($s['nama_lengkap']); ?></td>
                                    <td><?php echo htmlspecialchars($s['nama_kelas']); ?></td>
                                    <td>
                                        <select class="form-control" name="status[<?php echo $s['id']; ?>]">
                                            <option value="hadir" <?php echo $s['status'] == 'hadir' ? 'selected' : ''; ?>>Hadir</option>
                                            <option value="izin" <?php echo $s['status'] == 'izin' ? 'selected' : ''; ?>>Izin</option>
                                            <option value="sakit" <?php echo $s['status'] == 'sakit' ? 'selected' : ''; ?>>Sakit</option>
                                            <option value="alpha" <?php echo $s['status'] == 'alpha' ? 'selected' : ''; ?>>Alpha</option>
                                        </select>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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

<script>
function filterSiswa() {
    var kelasId = document.getElementById('filterKelas').value;
    window.location.href = 'absensi_sekolah.php' + (kelasId ? '?kelas_id=' + kelasId : '');
}
</script>

<?php include '../templates/footer.php'; ?> 