<?php
// Pastikan $user sudah diambil dari database sebelum include template ini
?>
<div class="content-wrapper">
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
            <?php if (isset($success_msg) && $success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_msg; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>

            <?php if (isset($error_msg) && $error_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_msg; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-3">
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <img class="profile-user-img img-fluid"
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
                            <form class="form-horizontal profile-form" action="" method="POST" enctype="multipart/form-data">
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
                    document.querySelector('.profile-user-img').src = e.target.result;
                    updateSidebarPhoto(e.target.result);
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
});
</script> 