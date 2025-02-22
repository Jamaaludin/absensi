<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="dashboard.php" class="brand-link">
        <img src="../assets/img/logo.jpg" alt="Logo" 
             class="brand-image img-circle elevation-3" 
             style="opacity: .8; width: 33px; height: auto; max-width: 100%;">
        <span class="brand-text font-weight-light">Sistem Absensi</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="<?php 
                    $cache_buster = isset($_GET['updated']) ? $_GET['updated'] : time();
                    echo isset($_SESSION['foto']) && !empty($_SESSION['foto']) 
                        ? '../assets/img/profile/' . $_SESSION['foto'] . '?v=' . $cache_buster
                        : '../assets/img/foto-awal.jpg'; 
                ?>" 
                    class="img-circle elevation-2" 
                    alt="User Image"
                    style="width: 33px; height: 33px; object-fit: cover;">
            </div>
            <div class="info">
                <a href="profile.php" class="d-block">
                    <?php echo $_SESSION['nama_lengkap']; ?>
                    <small class="d-block text-muted"><?php echo ucfirst($_SESSION['role']); ?></small>
                </a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <?php if($_SESSION['role'] == 'admin'): ?>
                <!-- Menu Admin -->
                <li class="nav-header">ADMINISTRASI</li>
                
                <li class="nav-item">
                    <a href="kelas.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'kelas.php' ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-school"></i>
                        <p>Manajemen Kelas</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="siswa.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'siswa.php' ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-user-graduate"></i>
                        <p>Manajemen Siswa</p>
                    </a>
                </li>

                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['guru.php', 'jam_mengajar.php', 'mata_pelajaran.php']) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-chalkboard-teacher"></i>
                        <p>
                            Manajemen Guru
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="mata_pelajaran.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'mata_pelajaran.php' ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Input Mata Pelajaran</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="guru.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'guru.php' ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Data Guru</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="jam_mengajar.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'jam_mengajar.php' ? 'active' : ''; ?>">
                                <i class="fas fa-clock nav-icon"></i>
                                <p>Lihat Jam Mengajar</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a href="users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Manajemen User</p>
                    </a>
                </li>
                <?php endif; ?>

                <!-- Menu untuk Wakasek -->
                <?php if($_SESSION['role'] == 'wakasek'): ?>
                    <!-- Menu Manajemen -->
                    <li class="nav-header">MANAJEMEN</li>
                    
                    <!-- Manajemen Kelas -->
                    <li class="nav-item has-treeview">
                        <a href="#" class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['kelas.php', 'wali_kelas.php']) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-school"></i>
                            <p>
                                Manajemen Kelas
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="kelas.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'kelas.php' ? 'active' : ''; ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Data Kelas</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="wali_kelas.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'wali_kelas.php' ? 'active' : ''; ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Set Wali Kelas</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Manajemen Siswa -->
                    <li class="nav-item has-treeview">
                        <a href="#" class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['siswa.php', 'siswa_tambah.php', 'siswa_import.php']) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-user-graduate"></i>
                            <p>
                                Manajemen Siswa
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="siswa.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'siswa.php' ? 'active' : ''; ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Data Siswa</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="siswa_tambah.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'siswa_tambah.php' ? 'active' : ''; ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Tambah Siswa</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="siswa_import.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'siswa_import.php' ? 'active' : ''; ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Import Data Siswa</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Manajemen Guru -->
                    <li class="nav-item has-treeview">
                        <a href="#" class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['guru.php', 'mata_pelajaran.php']) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-chalkboard-teacher"></i>
                            <p>
                                Manajemen Guru
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="mata_pelajaran.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'mata_pelajaran.php' ? 'active' : ''; ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Input Mata Pelajaran</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="guru.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'guru.php' ? 'active' : ''; ?>">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Data Guru</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Lihat Jam Mengajar -->
                    <li class="nav-item">
                        <a href="jam_mengajar.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'jam_mengajar.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-clock"></i>
                            <p>Lihat Jam Mengajar</p>
                        </a>
                    </li>

                    <!-- Menu Monitoring Absensi -->
                    <li class="nav-header">MONITORING ABSENSI</li>
                    <li class="nav-item has-treeview">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p>
                                Monitoring Absensi
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="lprn_absensi_sekolah.php" class="nav-link">
                                    <i class="nav-icon fas fa-graduation-cap"></i>
                                    <p>Absensi Sekolah</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="lprn_absensi_pengasuhan.php" class="nav-link">
                                    <i class="nav-icon fas fa-home"></i>
                                    <p>Absensi Pengasuhan</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Menu Absensi -->
                <li class="nav-header">ABSENSI</li>
                <?php if(in_array($_SESSION['role'], ['admin'])): ?>
                <li class="nav-item">
                    <a href="absensi_sekolah.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'absensi_sekolah.php' ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-clipboard-check"></i>
                        <p>Absensi Sekolah</p>
                    </a>
                </li>
                <?php endif; ?>

                <?php if(in_array($_SESSION['role'], ['admin', 'pengasuhan'])): ?>
                <li class="nav-item">
                    <a href="absensi_pengasuhan.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'absensi_pengasuhan.php' ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-bed"></i>
                        <p>Absensi Pengasuhan</p>
                    </a>
                </li>
                <?php endif; ?>

                <?php if(in_array($_SESSION['role'], ['guru', 'wali_kelas'])): ?>
                    <li class="nav-item">
                        <a href="jam_mengajar.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'jam_mengajar.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-clock"></i>
                            <p>Jadwal Mengajar</p>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if(in_array($_SESSION['role'], ['admin', 'guru', 'wali_kelas', 'pengasuhan'])): ?>
                <!-- Ubah menu Laporan Absensi menjadi dropdown -->
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-file-alt"></i>
                        <p>
                            Laporan Absensi
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if(!in_array($_SESSION['role'], ['pengasuhan'])): ?>
                        <li class="nav-item">
                            <a href="lprn_absensi_sekolah.php" class="nav-link">
                                <i class="nav-icon fas fa-graduation-cap"></i>
                                <p>Lpr Sekolah</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if(in_array($_SESSION['role'], ['admin', 'pengasuhan'])): ?>
                        <li class="nav-item">
                            <a href="lprn_absensi_pengasuhan.php" class="nav-link">
                                <i class="nav-icon fas fa-home"></i>
                                <p>Lpr Pengasuhan</p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Menu User -->
                <li class="nav-header">USER</li>
                <li class="nav-item">
                    <a href="profile.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-user"></i>
                        <p>Profile</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="../logout.php" class="nav-link" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                        <i class="nav-icon fas fa-sign-out-alt text-danger"></i>
                        <p class="text-danger">Logout</p>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside> 