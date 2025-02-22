<?php 
include '../templates/header.php';
include '../templates/sidebar.php';
require_once '../config/database.php';
checkRole(['admin']);

// Tambahkan pesan sukses/error
$success_msg = '';
$error_msg = '';

// Proses tambah/edit/hapus user
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch($_POST['action']) {
                case 'add':
                    $username = $_POST['username'];
                    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $role = $_POST['role'];
                    $nama_lengkap = $_POST['nama_lengkap'];
                    
                    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, nama_lengkap) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$username, $password, $role, $nama_lengkap]);
                    $success_msg = "User berhasil ditambahkan!";
                    break;
                    
                case 'edit':
                    $id = $_POST['id'];
                    $username = $_POST['username'];
                    $role = $_POST['role'];
                    $nama_lengkap = $_POST['nama_lengkap'];
                    
                    if (!empty($_POST['password'])) {
                        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET username=?, password=?, role=?, nama_lengkap=? WHERE id=?");
                        $stmt->execute([$username, $password, $role, $nama_lengkap, $id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET username=?, role=?, nama_lengkap=? WHERE id=?");
                        $stmt->execute([$username, $role, $nama_lengkap, $id]);
                    }
                    $success_msg = "User berhasil diupdate!";
                    break;
                    
                case 'delete':
                    $id = $_POST['id'];
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
                    $stmt->execute([$id]);
                    $success_msg = "User berhasil dihapus!";
                    break;
            }
        } catch (PDOException $e) {
            $error_msg = "Error: " . $e->getMessage();
        }
    }
}

// Ambil daftar user
try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY role, nama_lengkap");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_msg = "Error: " . $e->getMessage();
    $users = [];
}
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Manajemen User</h1>
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
                                <input type="text" class="form-control" id="searchNama" 
                                       placeholder="Cari berdasarkan nama..." onkeyup="filterUsers()">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addUserModal">
                                <i class="fas fa-plus"></i> Tambah User
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Username</th>
                                <th>Nama Lengkap</th>
                                <th>Role</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $index => $user): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['nama_lengkap']); ?></td>
                                <td><?php echo ucfirst($user['role']); ?></td>
                                <td>
                                    <button type="button" class="btn btn-warning btn-sm" 
                                            onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" 
                                            onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo $user['username']; ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
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

<!-- Modal Tambah User -->
<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Tambah User Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama_lengkap" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select class="form-control" name="role" required>
                            <option value="admin">Administrator</option>
                            <option value="wakasek">Wakasek</option>
                            <option value="guru">Guru</option>
                            <option value="pengasuhan">Pengasuhan</option>
                            <option value="siswa">Siswa</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit User -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" class="form-control" name="username" id="edit_username" required>
                    </div>
                    <div class="form-group">
                        <label>Password (Kosongkan jika tidak diubah)</label>
                        <input type="password" class="form-control" name="password">
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama_lengkap" id="edit_nama_lengkap" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select class="form-control" name="role" id="edit_role" required>
                            <option value="admin">Administrator</option>
                            <option value="wakasek">Wakasek</option>
                            <option value="guru">Guru</option>
                            <option value="pengasuhan">Pengasuhan</option>
                            <option value="siswa">Siswa</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form Hapus User -->
<form id="deleteForm" action="" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
</form>

<script>
function editUser(user) {
    document.getElementById('edit_id').value = user.id;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_nama_lengkap').value = user.nama_lengkap;
    document.getElementById('edit_role').value = user.role;
    $('#editUserModal').modal('show');
}

function deleteUser(id, username) {
    if (confirm('Apakah Anda yakin ingin menghapus user "' + username + '"?')) {
        document.getElementById('delete_id').value = id;
        document.getElementById('deleteForm').submit();
    }
}

function filterUsers() {
    const searchText = document.getElementById('searchNama').value.toLowerCase();
    const table = document.querySelector('table');
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) { // Mulai dari 1 untuk skip header
        const nama = rows[i].getElementsByTagName('td')[2].textContent.toLowerCase();
        if (nama.includes(searchText)) {
            rows[i].style.display = '';
        } else {
            rows[i].style.display = 'none';
        }
    }
}

// Inisialisasi tooltips jika ada
$(function () {
    $('[data-toggle="tooltip"]').tooltip();
});
</script>

<?php include '../templates/footer.php'; ?> 