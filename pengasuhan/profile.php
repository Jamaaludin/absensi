<?php
require_once '../config/init.php';
checkRole(['pengasuhan']);

// Setelah semua pengecekan header, baru include template
include '../templates/header.php';
include '../templates/sidebar.php';
require_once '../config/database.php';

// Inisialisasi variabel
$success_msg = '';
$error_msg = '';

// Ambil data user yang sedang login
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();

        // Validasi input
        if (empty($_POST['nama_lengkap']) || empty($_POST['username'])) {
            throw new Exception("Nama lengkap dan username harus diisi!");
        }

        // Update data dasar
        $sql = "UPDATE users SET 
                nama_lengkap = :nama_lengkap,
                username = :username,
                alamat = :alamat,
                no_telp = :no_telp";
        $params = [
            'nama_lengkap' => $_POST['nama_lengkap'],
            'username' => $_POST['username'],
            'alamat' => $_POST['alamat'],
            'no_telp' => $_POST['no_telp'],
            'id' => $_SESSION['user_id']
        ];

        // Jika ada password baru
        if (!empty($_POST['password'])) {
            $sql .= ", password = :password";
            $params['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Upload foto jika ada
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $filename = $_FILES['foto']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                throw new Exception("Format file harus JPG, JPEG, atau PNG!");
            }

            // Generate nama file unik
            $newName = date('YmdHis') . "." . $ext; // Format: YYYYMMDDHHIISS
            $destination = "../assets/img/profile/" . $newName;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $destination)) {
                // Hapus foto lama jika ada
                if (!empty($user['foto']) && file_exists("../assets/img/profile/" . $user['foto'])) {
                    unlink("../assets/img/profile/" . $user['foto']);
                }

                // Update nama file di database
                $stmt = $pdo->prepare("UPDATE users SET foto = ? WHERE id = ?");
                $stmt->execute([$newName, $_SESSION['user_id']]);

                // Update session
                $_SESSION['foto'] = $newName;
            }
        }

        $pdo->commit();
        $_SESSION['nama_lengkap'] = $_POST['nama_lengkap'];
        $success_msg = "Profil berhasil diupdate!";

        // Refresh data user setelah semua perubahan
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        // Redirect dengan parameter timestamp untuk memaksa refresh cache
        header("Location: profile.php?updated=" . time());
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $error_msg = $e->getMessage();
    }
}
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

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <!-- Profile Image -->
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <img src="<?php 
                                    $cache_buster = isset($_GET['updated']) ? $_GET['updated'] : time();
                                    echo isset($user['foto']) && !empty($user['foto']) 
                                        ? '../assets/img/profile/' . $user['foto'] . '?v=' . $cache_buster 
                                        : '../assets/img/foto-awal.jpg'; 
                                ?>" 
                                    class="profile-user-img img-fluid img-circle" 
                                    style="width: 200px; height: 200px; object-fit: cover;"
                                    id="preview-foto">
                            </div>
                            <h3 class="profile-username text-center"><?php echo htmlspecialchars($user['nama_lengkap']); ?></h3>
                            <p class="text-muted text-center">Pengasuhan</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
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
                        <div class="card-header p-2">
                            <h3 class="card-title p-1">Edit Profile</h3>
                        </div>
                        <div class="card-body">
                            <form action="" method="POST" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label>Username</label>
                                    <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Nama Lengkap</label>
                                    <input type="text" class="form-control" name="nama_lengkap" value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Password Baru</label>
                                    <input type="password" class="form-control" name="password" placeholder="Kosongkan jika tidak ingin mengubah password">
                                </div>
                                <div class="form-group">
                                    <label>Alamat</label>
                                    <textarea class="form-control" name="alamat" rows="3"><?php echo htmlspecialchars($user['alamat']); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>No. Telepon</label>
                                    <input type="text" class="form-control" name="no_telp" value="<?php echo htmlspecialchars($user['no_telp']); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Foto Profil</label>
                                    <input type="file" class="form-control" name="foto" accept="image/*" onchange="previewImage(this)">
                                    <small class="form-text text-muted">Format: JPG/PNG, Maks: 2MB</small>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">Update Profile</button>
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
            // Update preview foto utama
            const previewFoto = document.getElementById('preview-foto');
            previewFoto.src = e.target.result;
            
            // Update foto sidebar
            const sidebarFoto = document.querySelector('.user-panel .image img');
            if (sidebarFoto) {
                sidebarFoto.src = e.target.result;
            }
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include '../templates/footer.php'; ?> 