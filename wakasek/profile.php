<?php 
include '../templates/header.php';
include '../templates/sidebar.php';
require_once '../config/database.php';
checkRole(['wakasek']);

// Ambil data user yang sedang login
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Fungsi untuk membuat direktori jika belum ada
function createUploadDirectory($path) {
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
}

// Proses update profile
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $nama_lengkap = $_POST['nama_lengkap'];
        $username = $_POST['username'];
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $password = $_POST['password'];
        
        // Mulai transaction
        $pdo->beginTransaction();
        
        if (!empty($password)) {
            // Update dengan password baru
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ?, username = ?, jenis_kelamin = ?, password = ? WHERE id = ?");
            $stmt->execute([$nama_lengkap, $username, $jenis_kelamin, $hashed_password, $_SESSION['user_id']]);
        } else {
            // Update tanpa password
            $stmt = $pdo->prepare("UPDATE users SET nama_lengkap = ?, username = ?, jenis_kelamin = ? WHERE id = ?");
            $stmt->execute([$nama_lengkap, $username, $jenis_kelamin, $_SESSION['user_id']]);
        }

        // Upload foto jika ada
        if (!empty($_FILES['foto']['name'])) {
            $foto = $_FILES['foto'];
            $ext = pathinfo($foto['name'], PATHINFO_EXTENSION);
            $foto_name = 'wakasek_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
            
            // Buat direktori uploads jika belum ada
            $upload_dir = '../assets/img/profile/';
            createUploadDirectory($upload_dir);
            
            $foto_path = $upload_dir . $foto_name;

            // Validasi tipe file
            $allowed = ['jpg', 'jpeg', 'png'];
            if (!in_array(strtolower($ext), $allowed)) {
                throw new Exception('Tipe file tidak diizinkan. Hanya JPG, JPEG, dan PNG yang diperbolehkan.');
            }

            // Pindahkan file
            if (move_uploaded_file($foto['tmp_name'], $foto_path)) {
                // Hapus foto lama jika ada
                if (!empty($user['foto'])) {
                    $old_foto = $upload_dir . $user['foto'];
                    if (file_exists($old_foto)) {
                        unlink($old_foto);
                    }
                }
                
                // Update nama foto di database
                $stmt = $pdo->prepare("UPDATE users SET foto = ? WHERE id = ?");
                $stmt->execute([$foto_name, $_SESSION['user_id']]);
                
                // Update session foto
                $_SESSION['foto'] = $foto_name;
            } else {
                throw new Exception('Gagal mengupload file. Silakan coba lagi.');
            }
        }

        $pdo->commit();
        $_SESSION['success'] = "Profile berhasil diperbarui!";
        
        // Refresh data user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
    }
}

$foto = !empty($user['foto']) ? '../assets/img/profile/' . $user['foto'] : '../assets/img/profile/foto-awal.jpg';
?>

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
            
            <div class="row">
                <div class="col-md-4">
                    <!-- Profile Image -->
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <img class="profile-user-img img-fluid img-circle"
                                     src="<?php echo $foto; ?>"
                                     alt="User profile picture"
                                     id="preview-foto">
                            </div>
                            <h3 class="profile-username text-center"><?php echo htmlspecialchars($user['nama_lengkap']); ?></h3>
                            <p class="text-muted text-center">Wakil Kepala Sekolah</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Edit Profile</h3>
                        </div>
                        <div class="card-body">
                            <form class="form-horizontal" method="POST" enctype="multipart/form-data">
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
                                    <label class="col-sm-2 col-form-label">Jenis Kelamin</label>
                                    <div class="col-sm-10">
                                        <select class="form-control" name="jenis_kelamin" required>
                                            <option value="L" <?php echo $user['jenis_kelamin'] == 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                                            <option value="P" <?php echo $user['jenis_kelamin'] == 'P' ? 'selected' : ''; ?>>Perempuan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Password Baru</label>
                                    <div class="col-sm-10">
                                        <input type="password" class="form-control" name="password" placeholder="Kosongkan jika tidak ingin mengubah password">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Foto</label>
                                    <div class="col-sm-10">
                                        <input type="file" class="form-control-file" name="foto" accept="image/*" id="input-foto" onchange="previewImage(this)">
                                        <small class="form-text text-muted">Upload foto baru (JPG, JPEG, PNG)</small>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="offset-sm-2 col-sm-10">
                                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
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
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        
        reader.onload = function(e) {
            document.getElementById('preview-foto').src = e.target.result;
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include '../templates/footer.php'; ?> 