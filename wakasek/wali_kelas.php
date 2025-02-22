<?php 
include '../templates/header.php';
include '../templates/sidebar.php';
require_once '../config/database.php';
checkRole(['wakasek']);

// Proses update role guru menjadi wali kelas
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['set_wali_kelas'])) {
    try {
        $pdo->beginTransaction();
        
        // Reset role wali kelas yang lama untuk kelas yang dipilih
        $stmt = $pdo->prepare("UPDATE users SET role = 'guru' WHERE role = 'wali_kelas' AND id = (SELECT wali_kelas_id FROM kelas WHERE id = ?)");
        $stmt->execute([$_POST['kelas_id']]);
        
        // Update role guru menjadi wali kelas
        $stmt = $pdo->prepare("UPDATE users SET role = 'wali_kelas' WHERE id = ?");
        $stmt->execute([$_POST['guru_id']]);
        
        // Update wali kelas di tabel kelas
        $stmt = $pdo->prepare("UPDATE kelas SET wali_kelas_id = ? WHERE id = ?");
        $stmt->execute([$_POST['guru_id'], $_POST['kelas_id']]);
        
        $pdo->commit();
        $_SESSION['success'] = "Wali kelas berhasil diperbarui!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
    }
    
    header("Location: wali_kelas.php");
    exit();
}

// Ambil daftar kelas
$query = "SELECT k.*, u.nama_lengkap as wali_kelas 
          FROM kelas k 
          LEFT JOIN users u ON k.wali_kelas_id = u.id 
          ORDER BY k.nama_kelas";
$stmt = $pdo->query($query);
$kelas_list = $stmt->fetchAll();
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Set Wali Kelas</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Set Wali Kelas</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <?php 
            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon fas fa-check"></i> Sukses!</h5>
                        '.$_SESSION['success'].'
                      </div>';
                unset($_SESSION['success']);
            }
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon fas fa-ban"></i> Error!</h5>
                        '.$_SESSION['error'].'
                      </div>';
                unset($_SESSION['error']);
            }
            ?>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Kelas dan Wali Kelas</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr class="text-center">
                                <th style="width: 50px">No</th>
                                <th>Nama Kelas</th>
                                <th>Wali Kelas Saat Ini</th>
                                <th>Set Wali Kelas</th>
                                <th style="width: 100px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            foreach($kelas_list as $kelas): 
                                // Ambil daftar guru yang tersedia untuk kelas ini
                                $query = "SELECT id, nama_lengkap, mata_pelajaran 
                                          FROM users 
                                          WHERE (role = 'guru' OR id = ?)
                                          AND id NOT IN (
                                              SELECT wali_kelas_id 
                                              FROM kelas 
                                              WHERE wali_kelas_id IS NOT NULL
                                              AND id != ?
                                          )
                                          ORDER BY nama_lengkap";
                                $stmt = $pdo->prepare($query);
                                $stmt->execute([$kelas['wali_kelas_id'], $kelas['id']]);
                                $available_guru = $stmt->fetchAll();
                            ?>
                            <tr>
                                <td class="text-center"><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($kelas['nama_kelas']); ?></td>
                                <td><?php echo htmlspecialchars($kelas['wali_kelas'] ?? 'Belum ditentukan'); ?></td>
                                <td>
                                    <form method="POST" class="wali-kelas-form">
                                        <input type="hidden" name="kelas_id" value="<?php echo $kelas['id']; ?>">
                                        <select name="guru_id" class="form-control select-guru">
                                            <option value="">- Pilih Guru -</option>
                                            <?php foreach($available_guru as $guru): ?>
                                                <option value="<?php echo $guru['id']; ?>" 
                                                    <?php echo ($guru['id'] == $kelas['wali_kelas_id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($guru['nama_lengkap']); ?> 
                                                    (<?php echo htmlspecialchars($guru['mata_pelajaran']); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                </td>
                                <td class="text-center">
                                        <button type="submit" name="set_wali_kelas" class="btn btn-primary btn-sm">
                                            <i class="fas fa-save"></i> Simpan
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
    </section>
</div>

<!-- Select2 -->
<link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
<link rel="stylesheet" href="../assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
<script src="../assets/plugins/select2/js/select2.full.min.js"></script>

<script>
$(document).ready(function() {
    // Inisialisasi Select2
    $('.select-guru').select2({
        theme: 'bootstrap4',
        placeholder: "- Pilih Guru -",
        allowClear: true
    });
});
</script>

<?php include '../templates/footer.php'; ?> 