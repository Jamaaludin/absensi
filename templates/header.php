<?php
require_once '../config/init.php';

// Mendapatkan nama role untuk ditampilkan
function getRoleName($role) {
    switch($role) {
        case 'admin': return 'Administrator';
        case 'wakasek': return 'Wakil Kepala Sekolah';
        case 'guru': return 'Guru';
        case 'pengasuhan': return 'Pengasuhan';
        case 'siswa': return 'Siswa';
        default: return 'User';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Absensi - <?php echo getRoleName($_SESSION['role']); ?></title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../dist/css/adminlte.min.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="../plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
    <!-- Custom CSS -->
    <style>
        /* Style untuk foto profil di semua halaman */
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

        /* Style untuk foto profil di sidebar */
        .user-panel .image img {
            width: 33px;
            height: 33px;
            object-fit: cover;
            border-radius: 50%;
        }

        /* Style untuk form profile yang konsisten */
        .profile-form .form-group:last-of-type {
            margin-bottom: 0;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                    <i class="bi bi-list"></i>
                </a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->
</div>
</body>
</html> 