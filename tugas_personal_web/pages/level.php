<?php
// pages/level.php - TANPA session_start()
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Proses Tambah Level
if (isset($_POST['submit_level'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    if (!empty($nama)) {
        mysqli_query($conn, "INSERT INTO level (nama) VALUES ('$nama')");
        header("Location: ?page=level");
        exit();
    }
}

// Proses Hapus Level
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM level WHERE id = $id");
    header("Location: ?page=level");
    exit();
}

// PROSES UPDATE (EDIT) LEVEL - TAMBAHKAN INI!
if (isset($_POST['update_level'])) {
    $id = (int)$_POST['id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    mysqli_query($conn, "UPDATE level SET nama='$nama' WHERE id=$id");
    header("Location: ?page=level");
    exit();
}

// Ambil data untuk ditampilkan
$result = mysqli_query($conn, "SELECT * FROM level ORDER BY id");

// Ambil data yang akan diedit (jika ada parameter edit)
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit_result = mysqli_query($conn, "SELECT * FROM level WHERE id=$id");
    $edit_data = mysqli_fetch_assoc($edit_result);
}
?>

<!-- FORM EDIT DATA (muncul jika klik tombol Edit) -->
<?php if ($edit_data): ?>
<div class="card mb-3 border-warning">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="fas fa-edit"></i> Edit Level</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
            <div class="input-group">
                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($edit_data['nama']) ?>" required>
                <button type="submit" name="update_level" class="btn btn-warning">
                    <i class="fas fa-save"></i> Update
                </button>
                <a href="?page=level" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- FORM TAMBAH DATA -->
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-plus"></i> Tambah Level Baru</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="input-group">
                <input type="text" name="nama" class="form-control" placeholder="Nama Level (contoh: TK, SD, SMP, SMA)" required>
                <button type="submit" name="submit_level" class="btn btn-success">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- TABEL DATA LEVEL -->
<div class="card mt-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-table"></i> Daftar Level Pendidikan</h5>
    </div>
    <div class="card-body">
        <?php if (mysqli_num_rows($result) == 0): ?>
            <div class="alert alert-warning">Belum ada data level.</div>
        <?php else: ?>
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th width="10%">ID</th>
                        <th>Nama Level</th>
                        <th width="25%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td>
                            <a href="?page=level&edit=<?= $row['id'] ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="?page=level&delete=<?= $row['id'] ?>" 
                               class="btn btn-danger btn-sm" 
                               onclick="return confirm('Yakin hapus level <?= htmlspecialchars($row['nama']) ?>?')">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>