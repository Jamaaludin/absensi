<?php 
include '../templates/header.php';
include '../templates/sidebar.php';
require_once '../config/database.php';
checkRole(['pengasuhan']);

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
                $stmt = $pdo->prepare("SELECT id FROM absensi_pengasuhan WHERE user_id = ? AND tanggal = ?");
                $stmt->execute([$user_id, $tanggal]);
                $existing = $stmt->fetch();
                
                if ($existing) {
                    // Update absensi yang sudah ada
                    $stmt = $pdo->prepare("UPDATE absensi_pengasuhan SET status = ? WHERE id = ?");
                    $stmt->execute([$status, $existing['id']]);
                } else {
                    // Insert absensi baru
                    $stmt = $pdo->prepare("INSERT INTO absensi_pengasuhan (user_id, tanggal, status) VALUES (?, ?, ?)");
                    $stmt->execute([$user_id, $tanggal, $status]);
                }
            }
            
            $pdo->commit();
            $success_msg = "Absensi berhasil disimpan!";
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_msg = "Error: " . $e->getMessage();
    }
}

// Filter kelas
$kelas_id = isset($_GET['kelas_id']) ? $_GET['kelas_id'] : '';

// Query untuk mengambil daftar kelas
$query = "SELECT * FROM kelas ORDER BY nama_kelas";
$stmt = $pdo->query($query);
$kelas_list = $stmt->fetchAll();

// Query untuk mengambil data siswa
$query = "SELECT s.*, k.nama_kelas, 
          COALESCE(ap.status, 'hadir') as status,
          ap.id as absensi_id
          FROM users s
          LEFT JOIN kelas k ON s.kelas_id = k.id
          LEFT JOIN absensi_pengasuhan ap ON s.id = ap.user_id 
          AND DATE(ap.tanggal) = CURDATE()
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
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Absensi Pengasuhan</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Absensi Pengasuhan</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <?php if ($success_msg): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <?php echo $success_msg; ?>
            </div>
            <?php endif; ?>

            <?php if ($error_msg): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <?php echo $error_msg; ?>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Data Absensi Pengasuhan</h3>
                    <div class="card-tools">
                        <select class="form-control" id="filterKelas" onchange="filterSiswa()">
                            <option value="">Semua Kelas</option>
                            <?php foreach($kelas_list as $kelas): ?>
                            <option value="<?php echo $kelas['id']; ?>" <?php echo $kelas_id == $kelas['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($kelas['nama_kelas']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <input type="hidden" name="action" value="absensi">
                        <input type="hidden" name="tanggal" value="<?php echo date('Y-m-d'); ?>">
                        
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
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
                                        <td><?php echo htmlspecialchars($s['username']); ?></td>
                                        <td><?php echo htmlspecialchars($s['nama_lengkap']); ?></td>
                                        <td><?php echo htmlspecialchars($s['nama_kelas']); ?></td>
                                        <td>
                                            <select class="form-control" name="status[<?php echo $s['id']; ?>]">
                                                <option value="hadir" <?php echo $s['status'] == 'hadir' ? 'selected' : ''; ?>>Hadir</option>
                                                <option value="hl" <?php echo $s['status'] == 'hl' ? 'selected' : ''; ?>>HL</option>
                                                <option value="hn" <?php echo $s['status'] == 'hn' ? 'selected' : ''; ?>>HN</option>
                                                <option value="bawabah" <?php echo $s['status'] == 'bawabah' ? 'selected' : ''; ?>>Bawabah</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Simpan Absensi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
function filterSiswa() {
    var kelasId = document.getElementById('filterKelas').value;
    window.location.href = 'absensi_pengasuhan.php' + (kelasId ? '?kelas_id=' + kelasId : '');
}
</script>

<?php include '../templates/footer.php'; ?> 