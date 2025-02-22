        </div><!-- /.content-wrapper -->
        
        <footer class="main-footer">
            <strong>Copyright &copy; 2024 <a href="#">Sistem Absensi Siswa</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 1.0.0
            </div>
        </footer>
    </div><!-- ./wrapper -->

    <!-- jQuery -->
    <script src="../plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../dist/js/adminlte.min.js"></script>
    <!-- Select2 -->
    <script src="../plugins/select2/js/select2.full.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>

    <script>
    // Initialize Select2
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: 'Pilih...',
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() {
                    return "Data tidak ditemukan";
                },
                searching: function() {
                    return "Mencari...";
                }
            }
        });

        // Fix untuk modal + select2
        $('.modal').on('shown.bs.modal', function () {
            $(this).find('.select2').select2({
                theme: 'bootstrap4',
                dropdownParent: $(this),
                width: '100%'
            });
        });
    });
    </script>
</body>
</html> 