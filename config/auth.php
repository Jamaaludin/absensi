<?php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function hasRole($allowed_roles) {
    return in_array($_SESSION['role'], $allowed_roles);
}

function checkLogin() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit();
    }
}

function checkRole($allowed_roles) {
    if (!hasRole($allowed_roles)) {
        header('Location: ../403.php');
        exit();
    }
}

function getDashboardURL($role) {
    switch($role) {
        case 'admin':
            return 'admin/dashboard.php';
        case 'wakasek':
            return 'wakasek/dashboard.php';
        case 'guru':
            return 'guru/dashboard.php';
        case 'wali_kelas':
            return 'guru/dashboard.php'; // Wali kelas menggunakan dashboard guru
        case 'pengasuhan':
            return 'pengasuhan/dashboard.php';
        case 'siswa':
            return 'siswa/dashboard.php';
        default:
            return 'login.php';
    }
}
?> 