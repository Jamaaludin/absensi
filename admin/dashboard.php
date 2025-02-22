<?php 
include '../templates/header.php';
include '../templates/sidebar.php';
require_once '../config/database.php';
checkRole(['admin']);

// Mengambil total siswa
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'siswa'");
$totalSiswa = $stmt->fetch()['total'];

// Mengambil total guru
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'guru'");
$totalGuru = $stmt->fetch()['total'];

// Mengambil data absensi hari ini
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT 
    COUNT(*) as total_hadir,
    (SELECT COUNT(*) FROM users WHERE role = 'siswa') as total_siswa
    FROM absensi 
    WHERE tanggal = ? AND status = 'hadir'");
$stmt->execute([$today]);
$absensi = $stmt->fetch();

$totalHadir = $absensi['total_hadir'];
$persentaseHadir = $absensi['total_siswa'] > 0 ? 
    round(($totalHadir / $absensi['total_siswa']) * 100) : 0;

// Mengambil total ketidakhadiran hari ini
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM absensi WHERE tanggal = ? AND status != 'hadir'");
$stmt->execute([$today]);
$totalTidakHadir = $stmt->fetch()['total'];
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Dashboard Administrator</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Info boxes -->
            <div class="row">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Siswa</span>
                            <span class="info-box-number"><?php echo $totalSiswa; ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-user-tie"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Guru</span>
                            <span class="info-box-number"><?php echo $totalGuru; ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Absensi Hari Ini</span>
                            <span class="info-box-number"><?php echo $persentaseHadir; ?>%</span>
                            <span class="info-box-text small">(<?php echo $totalHadir; ?> dari <?php echo $absensi['total_siswa']; ?> siswa)</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Ketidakhadiran</span>
                            <span class="info-box-number"><?php echo $totalTidakHadir; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Aktivitas Terbaru</h3>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <!-- Timeline items -->
                                <div>
                                    <i class="fas fa-user bg-info"></i>
                                    <div class="timeline-item">
                                        <span class="time"><i class="fas fa-clock"></i> 5 mins ago</span>
                                        <h3 class="timeline-header">Guru Budi melakukan absensi kelas X-A</h3>
                                    </div>
                                </div>
                                <!-- More timeline items... -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Statistik Kehadiran Mingguan</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="kehadiranChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include '../templates/footer.php'; ?> 