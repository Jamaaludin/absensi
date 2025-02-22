<?php 
include '../templates/header.php';
include '../templates/sidebar.php';
checkRole(['pengasuhan']);
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Dashboard Pengasuhan</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Info boxes -->
            <div class="row">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-home"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Asrama</span>
                            <span class="info-box-number">4</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-bed"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Siswa di Asrama</span>
                            <span class="info-box-number">120</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include '../templates/footer.php'; ?> 