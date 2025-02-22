<?php
require_once '../config/init.php';
checkRole(['guru', 'wali_kelas']);

// Setelah semua pengecekan header, baru include template
include '../templates/header.php';
include '../templates/sidebar.php';
require_once '../config/database.php';

// Inisialisasi variabel
$success_msg = '';
$error_msg = '';

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        if (isset($_POST['action'])) {
            switch($_POST['action']) {
                case 'update_profile':
                    $id = $_SESSION['user_id'];
                    $nama_lengkap = isset($_POST['nama_lengkap']) ? $_POST['nama_lengkap'] : '';
                    $username = isset($_POST['username']) ? $_POST['username'] : '';
                    $alamat = isset($_POST['alamat']) ? $_POST['alamat'] : '';
                    $no_telp = isset($_POST['no_telp']) ? $_POST['no_telp'] : '';
                    
                    // Validasi data wajib
                    if (empty($nama_lengkap) || empty($username)) {
                        throw new Exception('Nama Lengkap dan Username wajib diisi!');
                    }
                    
                    // Cek apakah ada file foto yang diupload
                    if (!empty($_FILES['foto']['name'])) {
                        $foto = $_FILES['foto'];
                        $allowed_types = ['image/jpeg', 'image/png'];
                        $max_size = 2 * 1024 * 1024; // 2MB
                        
                        if (!in_array($foto['type'], $allowed_types)) {
                            throw new Exception('Tipe file tidak didukung. Gunakan JPG atau PNG.');
                        }
                        
                        if ($foto['size'] > $max_size) {
                            throw new Exception('Ukuran file terlalu besar. Maksimal 2MB.');
                        }
                        
                        // Generate nama file unik
                        $ext = pathinfo($foto['name'], PATHINFO_EXTENSION);
                        $filename = 'profile_' . $id . '_' . time() . '.' . $ext;
                        $destination = '../assets/img/profile/' . $filename;
                        
                        // Pindahkan file
                        if (move_uploaded_file($foto['tmp_name'], $destination)) {
                            // Hapus foto lama jika ada
                            if (!empty($user['foto'])) {
                                $old_file = '../assets/img/profile/' . $user['foto'];
                                if (file_exists($old_file)) {
                                    unlink($old_file);
                                }
                            }
                            
                            // Update database dengan foto baru
                            $stmt = $pdo->prepare("UPDATE users SET foto = ? WHERE id = ?");
                            $stmt->execute([$filename, $id]);
                            
                            // Update session foto
                            $_SESSION['foto'] = $filename;
                        } else {
                            throw new Exception('Gagal mengupload foto.');
                        }
                    }
                    
                    // Update data profil
                    if (!empty($_POST['password'])) {
                        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET username = ?, nama_lengkap = ?, password = ?, alamat = ?, no_telp = ? WHERE id = ?");
                        $stmt->execute([$username, $nama_lengkap, $password, $alamat, $no_telp, $id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET username = ?, nama_lengkap = ?, alamat = ?, no_telp = ? WHERE id = ?");
                        $stmt->execute([$username, $nama_lengkap, $alamat, $no_telp, $id]);
                    }
                    
                    // Update session
                    $_SESSION['nama_lengkap'] = $nama_lengkap;
                    
                    $success_msg = "Profil berhasil diupdate!";
                    break;
            }
            $pdo->commit();
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_msg = "Error: " . $e->getMessage();
    }
}

// Ambil data user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<!-- Tambahkan CSS khusus untuk foto profil -->
<style>
.profile-user-img {
    width: 200px;
    height: 200px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid #adb5bd;
    margin: 0 auto;
    display: block;
    padding: 3px;
    background: #fff;
}

.box-profile {
    text-align: center;
    padding: 20px;
}

.profile-username {
    margin-top: 15px;
}
</style>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Profile</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Profile</li>
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

            <div class="row">
                <div class="col-md-3">
                    <!-- Profile Image -->
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <img class="profile-user-img img-fluid img-circle"
                                     src="<?php echo isset($user['foto']) ? '../assets/img/profile/' . $user['foto'] : '../assets/img/user-default.png'; ?>"
                                     alt="User profile picture">
                            </div>
                            <h3 class="profile-username text-center"><?php echo htmlspecialchars($user['nama_lengkap']); ?></h3>
                            <p class="text-muted text-center"><?php echo ucfirst($user['role']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="card">
                        <div class="card-body">
                            <form class="form-horizontal" action="" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Username</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Nama Lengkap</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" name="nama_lengkap" value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Password Baru</label>
                                    <div class="col-sm-10">
                                        <input type="password" class="form-control" name="password" placeholder="Kosongkan jika tidak diubah">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Alamat</label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control" name="alamat" rows="3"><?php echo htmlspecialchars($user['alamat']); ?></textarea>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">No. Telp</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" name="no_telp" value="<?php echo htmlspecialchars($user['no_telp']); ?>">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Foto Profil</label>
                                    <div class="col-sm-10">
                                        <input type="file" class="form-control-file" name="foto" accept="image/jpeg,image/png">
                                        <small class="text-muted">Format: JPG/PNG, Maks: 2MB</small>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="offset-sm-2 col-sm-10">
                                        <button type="submit" class="btn btn-primary">Update Profile</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fungsi untuk update foto sidebar
    function updateSidebarPhoto(photoUrl) {
        const sidebarPhoto = document.querySelector('.user-panel .image img');
        if (sidebarPhoto) {
            sidebarPhoto.src = photoUrl;
        }
    }

    // Event listener untuk preview foto
    const fileInput = document.querySelector('input[name="foto"]');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Update foto preview
                    document.querySelector('.profile-user-img').src = e.target.result;
                    // Update foto sidebar
                    updateSidebarPhoto(e.target.result);
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    // Update foto sidebar jika ada perubahan
    <?php if (isset($_SESSION['foto']) && !empty($_SESSION['foto'])): ?>
    updateSidebarPhoto('../assets/img/profile/<?php echo $_SESSION['foto']; ?>');
    <?php endif; ?>
});
</script>

<?php include '../templates/footer.php'; ?> 