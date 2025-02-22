<?php
session_start();
require_once 'config/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 Forbidden Access</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
</head>
<body class="hold-transition">
    <div class="wrapper">
        <div class="error-page" style="margin-top: 150px;">
            <h2 class="headline text-warning"> 403</h2>
            <div class="error-content">
                <h3><i class="fas fa-exclamation-triangle text-warning"></i> Oops! Akses Ditolak.</h3>
                <p>
                    Anda tidak memiliki akses ke halaman ini.
                    <?php if (isset($_SESSION['role'])): ?>
                        Silakan kembali ke <a href="<?php echo getDashboardURL($_SESSION['role']); ?>">dashboard</a>
                        atau <a href="logout.php">logout</a>.
                    <?php else: ?>
                        Silakan <a href="login.php">login</a> terlebih dahulu.
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="dist/js/adminlte.min.js"></script>
</body>
</html> 