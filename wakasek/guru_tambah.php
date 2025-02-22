<?php
include '../templates/header.php';
include '../templates/sidebar.php';
require_once '../config/database.php';
checkRole(['wakasek']);

// Ambil daftar mata pelajaran
$query = "SELECT * FROM mata_pelajaran ORDER BY nama";
$stmt = $pdo->query($query);
$mapel_list = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        $username = $_POST['username'];
        $nama_lengkap = $_POST['nama_lengkap'];
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $no_telp = $_POST['no_telp'];
        $mata_pelajaran = implode(', ', $_POST['mata_pelajaran']); // Gabungkan array jadi string
        $role = $_POST['role'];
        $kelas_id = !empty($_POST['kelas_id']) ? $_POST['kelas_id'] : null;
        
        // Set password default
        $password = password_hash('123456', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, jenis_kelamin, no_telp, mata_pelajaran, role, kelas_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$username, $password, $nama_lengkap, $jenis_kelamin, $no_telp, $mata_pelajaran, $role, $kelas_id]);
        
        $pdo->commit();
        $_SESSION['success_msg'] = "Data guru berhasil ditambahkan!";
        header("Location: guru.php");
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_msg = "Error: " . $e->getMessage();
    }
}

// Ambil daftar kelas untuk wali kelas
$query = "SELECT * FROM kelas ORDER BY nama_kelas";
$stmt = $pdo->query($query);
$kelas_list = $stmt->fetchAll();
?>

<!-- Form tambah guru -->
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Tambah Guru</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="guru.php">Data Guru</a></li>
                        <li class="breadcrumb-item active">Tambah Guru</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <form action="" method="POST">
                    <div class="card-body">
                        <div class="form-group">
                            <label>NIP</label>
                            <input type="text" class="form-control" name="username" required>
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
                            <label>No. Telepon</label>
                            <input type="text" class="form-control" name="no_telp">
                        </div>
                        <div class="form-group" id="mapel_container">
                            <label>Mata Pelajaran</label>
                            <div class="mapel-input-group mb-2">
                                <div class="input-group">
                                    <select class="form-control" name="mata_pelajaran[]" required>
                                        <option value="">Pilih Mata Pelajaran</option>
                                        <?php foreach ($mapel_list as $mapel): ?>
                                        <option value="<?php echo htmlspecialchars($mapel['nama']); ?>">
                                            <?php echo htmlspecialchars($mapel['nama']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-success" onclick="addMapelField()">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" name="role" id="role" required>
                                <option value="guru">Guru</option>
                                <option value="wali_kelas">Wali Kelas</option>
                            </select>
                        </div>
                        <div class="form-group" id="kelas_group" style="display: none;">
                            <label>Kelas</label>
                            <select class="form-control" name="kelas_id">
                                <option value="">Pilih Kelas</option>
                                <?php foreach ($kelas_list as $kelas): ?>
                                <option value="<?php echo $kelas['id']; ?>"><?php echo $kelas['nama_kelas']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="guru.php" class="btn btn-default">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<script>
document.getElementById('role').addEventListener('change', function() {
    const kelasGroup = document.getElementById('kelas_group');
    if (this.value === 'wali_kelas') {
        kelasGroup.style.display = 'block';
        kelasGroup.querySelector('select').required = true;
    } else {
        kelasGroup.style.display = 'none';
        kelasGroup.querySelector('select').required = false;
    }
});

function addMapelField() {
    const container = document.getElementById('mapel_container');
    const newGroup = document.createElement('div');
    newGroup.className = 'mapel-input-group mb-2';
    
    newGroup.innerHTML = `
        <div class="input-group">
            <select class="form-control" name="mata_pelajaran[]" required>
                <option value="">Pilih Mata Pelajaran</option>
                <?php foreach ($mapel_list as $mapel): ?>
                <option value="<?php echo htmlspecialchars($mapel['nama']); ?>">
                    <?php echo htmlspecialchars($mapel['nama']); ?>
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
    
    container.appendChild(newGroup);
}

function removeMapelField(button) {
    button.closest('.mapel-input-group').remove();
}
</script>

<style>
.mapel-input-group {
    margin-bottom: 10px;
}
.mapel-input-group:first-child .btn-danger {
    display: none;
}
</style>

<?php include '../templates/footer.php'; ?> 