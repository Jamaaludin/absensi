<?php 
include '../templates/header.php';
include '../templates/sidebar.php';
require_once '../config/database.php';
checkRole(['wakasek']);

// Inisialisasi variabel
$success_msg = '';
$error_msg = '';

// Tampilkan pesan dari session jika ada
if (isset($_SESSION['success_msg'])) {
    $success_msg = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}

if (isset($_SESSION['error_msg'])) {
    $error_msg = $_SESSION['error_msg'];
    unset($_SESSION['error_msg']);
}

// Query untuk mengambil data guru
$query = "SELECT u.*, 
          k.nama_kelas,
          (SELECT GROUP_CONCAT(CONCAT(mp.nama, ' (', mp.kode, ')')) 
           FROM mata_pelajaran mp 
           WHERE mp.nama = u.mata_pelajaran) as mapel_nama
          FROM users u 
          LEFT JOIN kelas k ON u.kelas_id = k.id 
          WHERE u.role IN ('guru', 'wali_kelas')
          ORDER BY u.nama_lengkap";
$stmt = $pdo->query($query);
$guru_list = $stmt->fetchAll();

// Query untuk mengambil daftar mata pelajaran
$query = "SELECT * FROM mata_pelajaran ORDER BY nama";
$stmt = $pdo->query($query);
$mapel_list = $stmt->fetchAll();
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Data Guru</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Data Guru</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <?php if ($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_msg; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>

            <?php if ($error_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_msg; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchInput" onkeyup="searchTable()" placeholder="Cari nama guru...">
                            </div>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="guru_tambah.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Guru
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover" id="guruTable">
                        <thead>
                            <tr class="text-center bg-light">
                                <th style="width: 50px">No</th>
                                <th style="width: 80px">Foto</th>
                                <th style="width: 120px">NIP</th>
                                <th>Nama Lengkap</th>
                                <th>Mata Pelajaran</th>
                                <th style="width: 100px">Jenis Kelamin</th>
                                <th style="width: 120px">No. Tlp</th>
                                <th style="width: 150px">Status</th>
                                <th style="width: 120px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            foreach($guru_list as $guru): 
                                $foto_path = !empty($guru['foto']) && file_exists('../assets/img/profile/' . $guru['foto']) 
                                    ? '../assets/img/profile/' . $guru['foto'] 
                                    : '../assets/img/foto-awal.jpg';
                                $status_badge = $guru['role'] == 'wali_kelas' 
                                    ? '<div class="text-center"><span class="badge badge-success px-3 py-2">Wali Kelas</span><br><small class="text-muted mt-1">' . htmlspecialchars($guru['nama_kelas']) . '</small></div>'
                                    : '<div class="text-center"><span class="badge badge-secondary px-3 py-2">Bukan Wali Kelas</span></div>';
                            ?>
                            <tr>
                                <td class="text-center align-middle"><?php echo $no++; ?></td>
                                <td class="text-center">
                                    <img src="<?php echo $foto_path; ?>" 
                                         alt="Foto <?php echo htmlspecialchars($guru['nama_lengkap']); ?>" 
                                         class="img-circle" 
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                </td>
                                <td class="align-middle"><?php echo htmlspecialchars($guru['username']); ?></td>
                                <td class="align-middle font-weight-bold"><?php echo htmlspecialchars($guru['nama_lengkap']); ?></td>
                                <td class="align-middle">
                                    <?php 
                                    if (!empty($guru['mata_pelajaran'])) {
                                        $mapel_array = explode(', ', $guru['mata_pelajaran']);
                                        foreach($mapel_array as $mapel) {
                                            echo '<span class="badge badge-info mr-1 mb-1">' . htmlspecialchars($mapel) . '</span>';
                                        }
                                    } else {
                                        echo '<span class="text-muted">-</span>';
                                    }
                                    ?>
                                </td>
                                <td class="text-center align-middle">
                                    <?php echo $guru['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?>
                                </td>
                                <td class="align-middle text-center">
                                    <?php 
                                    if (!empty($guru['no_telp'])) {
                                        echo '<span class="badge badge-light border px-2 py-1">' . htmlspecialchars($guru['no_telp']) . '</span>';
                                    } else {
                                        echo '<span class="text-muted">-</span>';
                                    }
                                    ?>
                                </td>
                                <td class="align-middle"><?php echo $status_badge; ?></td>
                                <td class="text-center align-middle">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-info btn-sm" onclick="viewGuru(<?php echo htmlspecialchars(json_encode($guru)); ?>)" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-warning btn-sm" onclick="editGuru(<?php echo htmlspecialchars(json_encode($guru)); ?>)" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm" onclick='pilihMapel(<?php 
                                            $guruData = [
                                                'id' => $guru['id'],
                                                'nama' => $guru['nama_lengkap'],
                                                'mapel' => !empty($guru['mata_pelajaran']) ? array_map('trim', explode(', ', $guru['mata_pelajaran'])) : []
                                            ];
                                            echo htmlspecialchars(json_encode($guruData, JSON_HEX_APOS | JSON_HEX_QUOT)); 
                                        ?>)' title="Jam Mengajar">
                                            <i class="fas fa-clock"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteGuru('<?php echo $guru['id']; ?>', '<?php echo htmlspecialchars($guru['nama_lengkap']); ?>')" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal View -->
<div class="modal fade" id="viewModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Detail Guru</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="30%">NIP</th>
                        <td id="view_nip"></td>
                    </tr>
                    <tr>
                        <th>Nama Lengkap</th>
                        <td id="view_nama_lengkap"></td>
                    </tr>
                    <tr>
                        <th>Mata Pelajaran</th>
                        <td id="view_mata_pelajaran"></td>
                    </tr>
                    <tr>
                        <th>Jenis Kelamin</th>
                        <td id="view_jenis_kelamin"></td>
                    </tr>
                    <tr>
                        <th>No. Telp</th>
                        <td id="view_no_telp"></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td id="view_status"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Form untuk delete -->
<form id="deleteForm" action="delete_guru.php" method="POST" style="display: none;">
    <input type="hidden" name="id" id="delete_id">
</form>

<!-- Tambahkan modal pilih mata pelajaran -->
<div class="modal fade" id="modalPilihMapel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Pilih Mata Pelajaran</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="list-group">
                    <!-- List mata pelajaran akan diisi melalui JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update modal edit -->
<div class="modal fade" id="editModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Guru</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editForm" onsubmit="submitEditForm(event)">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>NIP</label>
                        <input type="text" class="form-control" name="username" id="edit_username" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama_lengkap" id="edit_nama_lengkap" required>
                    </div>
                    <div class="form-group">
                        <label>Jenis Kelamin</label>
                        <select class="form-control" name="jenis_kelamin" id="edit_jenis_kelamin" required>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>No. Telepon</label>
                        <input type="text" class="form-control" name="no_telp" id="edit_no_telp">
                    </div>
                    <div class="form-group" id="edit_mapel_container">
                        <label>Mata Pelajaran</label>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Fungsi untuk view guru
function viewGuru(guru) {
    document.getElementById('view_nip').textContent = guru.username;
    document.getElementById('view_nama_lengkap').textContent = guru.nama_lengkap;
    document.getElementById('view_mata_pelajaran').textContent = guru.mapel_nama || '-';
    document.getElementById('view_jenis_kelamin').textContent = guru.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
    document.getElementById('view_no_telp').textContent = guru.no_telp || '-';
    
    let status = guru.role === 'wali_kelas' 
        ? 'Wali Kelas ' + (guru.nama_kelas || '')
        : 'Bukan Wali Kelas';
    document.getElementById('view_status').textContent = status;
    
    $('#viewModal').modal('show');
}

// Fungsi untuk edit guru
function editGuru(guru) {
    // Reset form
    document.getElementById('editForm').reset();
    
    // Set nilai-nilai form
    document.getElementById('edit_id').value = guru.id;
    document.getElementById('edit_username').value = guru.username;
    document.getElementById('edit_nama_lengkap').value = guru.nama_lengkap;
    document.getElementById('edit_jenis_kelamin').value = guru.jenis_kelamin;
    document.getElementById('edit_no_telp').value = guru.no_telp || '';
    
    // Reset container mata pelajaran
    const container = document.getElementById('edit_mapel_container');
    container.innerHTML = '<label>Mata Pelajaran</label>';
    
    // Parse mata pelajaran yang ada
    const mapelArray = guru.mata_pelajaran ? guru.mata_pelajaran.split(', ') : [];
    console.log('Mata pelajaran yang akan diedit:', mapelArray); // Debug
    
    if (mapelArray.length > 0) {
        mapelArray.forEach((mapel, index) => {
            addEditMapelField(mapel.trim());
        });
    } else {
        addEditMapelField();
    }
    
    $('#editModal').modal('show');
}

function addEditMapelField(selectedValue = '') {
    const container = document.getElementById('edit_mapel_container');
    const groups = container.getElementsByClassName('mapel-input-group');
    const isFirst = groups.length === 0;
    
    const div = document.createElement('div');
    div.className = 'mapel-input-group mb-2';
    div.innerHTML = `
        <div class="input-group">
            <select class="form-control" name="mata_pelajaran[]" required>
                <option value="">Pilih Mata Pelajaran</option>
                <?php foreach ($mapel_list as $mapel): ?>
                <option value="<?php echo htmlspecialchars($mapel['nama']); ?>"
                    ${selectedValue === '<?php echo htmlspecialchars($mapel['nama']); ?>' ? 'selected' : ''}>
                    <?php echo htmlspecialchars($mapel['nama']); ?>
                </option>
                <?php endforeach; ?>
            </select>
            <div class="input-group-append">
                ${isFirst ? 
                    `<button type="button" class="btn btn-success" onclick="addEditMapelField()">
                        <i class="fas fa-plus"></i>
                    </button>` :
                    `<button type="button" class="btn btn-danger" onclick="removeMapelField(this)">
                        <i class="fas fa-minus"></i>
                    </button>`
                }
            </div>
        </div>
    `;
    container.appendChild(div);
}

// Tambahkan fungsi removeMapelField
function removeMapelField(button) {
    const container = document.getElementById('edit_mapel_container');
    const groups = container.getElementsByClassName('mapel-input-group');
    
    // Jangan hapus jika hanya tersisa satu field
    if (groups.length > 1) {
        button.closest('.mapel-input-group').remove();
    }
}

// Fungsi untuk delete guru
function deleteGuru(id, nama) {
    Swal.fire({
        title: 'Hapus Guru?',
        text: `Anda yakin ingin menghapus data guru "${nama}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete_id').value = id;
            document.getElementById('deleteForm').submit();
        }
    });
}

// Fungsi untuk pencarian
function searchTable() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const table = document.getElementById('guruTable');
    const tr = table.getElementsByTagName('tr');

    for (let i = 1; i < tr.length; i++) {
        const td = tr[i].getElementsByTagName('td')[3]; // Index 3 adalah kolom Nama Lengkap
        if (td) {
            const txtValue = td.textContent || td.innerText;
            if (txtValue.toLowerCase().indexOf(input) > -1) {
                tr[i].style.display = '';
            } else {
                tr[i].style.display = 'none';
            }
        }
    }
}

function pilihMapel(guru) {
    // Reset dan bersihkan list
    const listGroup = document.querySelector('#modalPilihMapel .list-group');
    listGroup.innerHTML = '';
    
    // Debug
    console.log('Data guru yang diterima:', guru);
    console.log('Mata pelajaran:', guru.mapel);
    
    if (Array.isArray(guru.mapel)) {
        guru.mapel.forEach((mapel, index) => {
            const mapelTrim = mapel.trim();
            const listItem = document.createElement('div');
            listItem.className = 'list-group-item list-group-item-action';
            listItem.style.cursor = 'pointer';
            listItem.innerHTML = `
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1">${mapelTrim}</h5>
                </div>
                <small class="text-muted">Klik untuk melihat/mengatur jam mengajar</small>
            `;
            
            // Gunakan fungsi navigasi terpisah
            listItem.onclick = function() {
                navigateToJadwal(guru.id, mapelTrim);
            };
            
            listGroup.appendChild(listItem);
        });
    } else {
        listGroup.innerHTML = `
            <div class="list-group-item text-center text-muted">
                Tidak ada mata pelajaran yang ditambahkan
            </div>
        `;
    }
    
    // Tampilkan modal
    $('#modalPilihMapel').modal('show');
}

// Fungsi terpisah untuk navigasi
function navigateToJadwal(guruId, mapel) {
    // Tutup modal
    $('#modalPilihMapel').modal('hide');
    
    setTimeout(function() {
        // Buat URL dengan parameter
        const url = 'kelola_jam_mengajar.php?guru_id=' + guruId + '&mapel=' + encodeURIComponent(mapel);
        
        // Redirect ke halaman jam mengajar
        window.location.href = url;
    }, 500);
}

function submitEditForm(event) {
    event.preventDefault();
    
    const form = document.getElementById('editForm');
    const formData = new FormData(form);
    
    // Debug: log form data
    console.log('Form data yang akan dikirim:');
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    // Submit form menggunakan AJAX
    fetch('guru_edit.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text().then(text => {
            console.log('Response dari server:', text); // Debug response
            try {
                return JSON.parse(text);
            } catch (error) {
                console.error('Response text:', text);
                throw new Error('Invalid JSON response: ' + text);
            }
        });
    })
    .then(data => {
        $('#editModal').modal('hide');
        
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message,
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.reload();
            });
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        $('#editModal').modal('hide');
        
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: error.message || 'Terjadi kesalahan saat menyimpan data'
        });
    });
}
</script>

<!-- Tambahkan CSS custom -->
<style>
.badge-pink {
    background-color: #ff69b4;
    color: white;
}
.table thead th {
    vertical-align: middle;
    background-color: #f4f6f9;
}
.btn-group .btn {
    margin: 0 2px;
}
.badge {
    font-weight: 500;
    font-size: 0.85rem;
}
.badge-info {
    padding: 5px 10px;
}
.table td {
    vertical-align: middle;
}
.list-group-item:hover {
    background-color: #f8f9fa;
}
</style>

<?php include '../templates/footer.php'; ?> 