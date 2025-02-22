<?php
session_start();
require_once 'config/database.php';

// Jika user sudah login, redirect ke dashboard sesuai role
if (isset($_SESSION['user_id'])) {
    switch($_SESSION['role']) {
        case 'admin':
            header('Location: admin/dashboard.php');
            break;
        case 'wakasek':
            header('Location: wakasek/dashboard.php');
            break;
        case 'guru':
            header('Location: guru/dashboard.php');
            break;
        case 'pengasuhan':
            header('Location: pengasuhan/dashboard.php');
            break;
        case 'siswa':
            header('Location: siswa/dashboard.php');
            break;
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['foto'] = $user['foto'];
        
        // Redirect berdasarkan role
        switch($user['role']) {
            case 'admin':
                header('Location: admin/dashboard.php');
                break;
            case 'wakasek':
                header('Location: wakasek/dashboard.php');
                break;
            case 'guru':
                header('Location: guru/dashboard.php');
                break;
            case 'wali_kelas':
                header('Location: guru/dashboard.php'); // Wali kelas menggunakan dashboard guru
                break;
            case 'pengasuhan':
                header('Location: pengasuhan/dashboard.php');
                break;
            case 'siswa':
                header('Location: siswa/dashboard.php');
                break;
        }
        exit();
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Absensi Siswa</title>
    <!-- AdminLTE 4 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-alpha3/dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .login-page {
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .login-box {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="login-page">
    <div class="login-box">
        <div class="login-logo p-4">
            <b>Sistem Absensi</b> Siswa
        </div>
        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">Masuk untuk memulai sesi Anda</p>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="" method="post">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="username" placeholder="Username" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" name="password" placeholder="Password" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-8">
                            <div class="icheck-primary">
                                <input type="checkbox" id="remember">
                                <label for="remember">
                                    Ingat Saya
                                </label>
                            </div>
                        </div>
                        <div class="col-4">
                            <button type="submit" class="btn btn-primary btn-block">Masuk</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-alpha3/dist/js/adminlte.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Validasi form
        $('form').on('submit', function(e) {
            const username = $('input[name="username"]').val();
            const password = $('input[name="password"]').val();
            
            if (!username || !password) {
                e.preventDefault();
                alert('Semua field harus diisi!');
                return false;
            }
        });
    });
    </script>
</body>
</html> 