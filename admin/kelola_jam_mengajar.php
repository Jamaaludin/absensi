<?php 
include '../templates/header.php';
include '../templates/sidebar.php';
require_once '../config/database.php';
checkRole(['admin']);

// Ambil parameter dari URL
$guru_id = isset($_GET['guru_id']) ? $_GET['guru_id'] : null;
$mapel = isset($_GET['mapel']) ? $_GET['mapel'] : null;

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

// Ambil jadwal mengajar guru ini untuk mapel tertentu
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
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Kelola Jadwal Mengajar</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="guru.php">Guru</a></li>
                        <li class="breadcrumb-item active">Jadwal Mengajar</li>
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
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
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
                        <br>
                        <small class="text-muted">Mata Pelajaran: <?php echo htmlspecialchars($mapel); ?></small>
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addJadwalModal">
                            <i class="fas fa-plus"></i> Tambah Jadwal
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Hari</th>
                                <th>Jam Ke</th>
                                <th>Kelas</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($jadwal_list as $index => $jadwal): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($jadwal['nama_hari']); ?></td>
                                <td><?php echo htmlspecialchars($jadwal['jam']); ?></td>
                                <td><?php echo htmlspecialchars($jadwal['nama_kelas']); ?></td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm" 
                                            onclick="hapusJadwal(<?php echo $jadwal['id']; ?>)">
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

<!-- Modal Tambah Jadwal -->
<div class="modal fade" id="addJadwalModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Jadwal</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formJadwal" action="simpan_jadwal.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="guru_id" value="<?php echo $guru_id; ?>">
                    <input type="hidden" name="mata_pelajaran" value="<?php echo htmlspecialchars($mapel); ?>">
                    
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

<!-- Form untuk hapus jadwal -->
<form id="formHapus" action="hapus_jadwal.php" method="POST" style="display: none;">
    <input type="hidden" name="id" id="hapus_id">
    <input type="hidden" name="guru_id" value="<?php echo $guru_id; ?>">
    <input type="hidden" name="mapel" value="<?php echo htmlspecialchars($mapel); ?>">
</form>

<script>
// Fungsi untuk cek bentrok jadwal
function checkJadwalBentrok(kelas_id, hari, jam) {
    return new Promise((resolve, reject) => {
        $.post('check_jadwal_bentrok.php', {
            kelas_id: kelas_id,
            hari: hari,
            jam: jam,
            guru_id: <?php echo $guru_id; ?>
        })
        .done(function(response) {
            resolve(response);
        })
        .fail(function(xhr) {
            reject(xhr.responseText);
        });
    });
}

// Handler submit form jadwal
$('#formJadwal').on('submit', async function(e) {
    e.preventDefault();
    
    const kelas_id = $('select[name="kelas_id"]').val();
    const hari = $('select[name="hari"]').val();
    const jam = $('input[name="jam"]').val();
    
    try {
        const response = await checkJadwalBentrok(kelas_id, hari, jam);
        if (response.bentrok) {
            alert(response.message);
            return;
        }
        
        // Jika tidak bentrok, submit form
        this.submit();
    } catch (error) {
        alert('Terjadi kesalahan saat memeriksa jadwal');
        console.error(error);
    }
});

// Fungsi untuk hapus jadwal
function hapusJadwal(id) {
    if (confirm('Apakah Anda yakin ingin menghapus jadwal ini?')) {
        document.getElementById('hapus_id').value = id;
        document.getElementById('formHapus').submit();
    }
}
</script>

<?php include '../templates/footer.php'; ?> 