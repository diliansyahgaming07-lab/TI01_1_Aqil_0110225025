<?php
// pages/studies.php - TANPA session_start()
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Proses Simpan Data (CREATE)
if (isset($_POST['submit_study'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $id_level = (int)$_POST['id_level'];
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $tahun_lulus = !empty($_POST['tahun_lulus']) ? (int)$_POST['tahun_lulus'] : 'NULL';
    
    $foto_sekolah = '';
    if ($_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_name = time() . "_" . basename($_FILES["foto"]["name"]);
        $target_file = $target_dir . $file_name;
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (in_array($ext, ['jpg','jpeg','png','gif']) && $_FILES["foto"]["size"] < 2000000) {
            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
                $foto_sekolah = $file_name;
            }
        }
    }
    
    if ($tahun_lulus === 'NULL') {
        $sql = "INSERT INTO studies (nama, id_level, keterangan, tahun_lulus, foto_sekolah) 
                VALUES ('$nama', $id_level, '$keterangan', NULL, '$foto_sekolah')";
    } else {
        $sql = "INSERT INTO studies (nama, id_level, keterangan, tahun_lulus, foto_sekolah) 
                VALUES ('$nama', $id_level, '$keterangan', $tahun_lulus, '$foto_sekolah')";
    }
    
    if (mysqli_query($conn, $sql)) {
        header("Location: ?page=studies&success=1");
        exit();
    }
}

// PROSES UPDATE (EDIT) STUDIES - TAMBAHKAN INI!
if (isset($_POST['update_study'])) {
    $id = (int)$_POST['id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $id_level = (int)$_POST['id_level'];
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $tahun_lulus = !empty($_POST['tahun_lulus']) ? (int)$_POST['tahun_lulus'] : 'NULL';
    
    // Update tanpa mengganti foto
    $sql = "UPDATE studies SET 
            nama='$nama', 
            id_level=$id_level, 
            keterangan='$keterangan', 
            tahun_lulus=$tahun_lulus 
            WHERE id=$id";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: ?page=studies&updated=1");
        exit();
    } else {
        $error = "Gagal update: " . mysqli_error($conn);
    }
}

// Proses Hapus Data (DELETE)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $result = mysqli_query($conn, "SELECT foto_sekolah FROM studies WHERE id=$id");
    $data = mysqli_fetch_assoc($result);
    
    if ($data && $data['foto_sekolah']) {
        $foto_path = "uploads/" . $data['foto_sekolah'];
        if (file_exists($foto_path)) unlink($foto_path);
    }
    
    mysqli_query($conn, "DELETE FROM studies WHERE id=$id");
    header("Location: ?page=studies&deleted=1");
    exit();
}

// Ambil data untuk edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit_result = mysqli_query($conn, "SELECT * FROM studies WHERE id=$id");
    $edit_data = mysqli_fetch_assoc($edit_result);
}

// Ambil semua data studies
$query = "SELECT studies.*, level.nama as level_nama 
          FROM studies 
          JOIN level ON studies.id_level = level.id 
          ORDER BY studies.id DESC";
$result = mysqli_query($conn, $query);
?>

<!-- FORM EDIT DATA (muncul jika klik tombol Edit) -->
<?php if ($edit_data): ?>
<div class="card mb-4 border-warning">
    <div class="card-header bg-warning text-dark">
        <h4 class="mb-0"><i class="fas fa-edit"></i> Edit Riwayat Studi</h4>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Nama Sekolah</label>
                    <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($edit_data['nama']) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Jenjang (Level)</label>
                    <select name="id_level" class="form-select" required>
                        <option value="">Pilih Level</option>
                        <?php
                        $levels = mysqli_query($conn, "SELECT * FROM level ORDER BY id");
                        while($level = mysqli_fetch_assoc($levels)):
                        ?>
                        <option value="<?= $level['id'] ?>" <?= $level['id'] == $edit_data['id_level'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($level['nama']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label>Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="3"><?= htmlspecialchars($edit_data['keterangan']) ?></textarea>
            </div>
            <div class="mb-3">
                <label>Tahun Lulus</label>
                <input type="number" name="tahun_lulus" class="form-control" value="<?= $edit_data['tahun_lulus'] ?>">
            </div>
            <div class="mb-3">
                <label>Foto Saat Ini</label><br>
                <?php if ($edit_data['foto_sekolah'] && file_exists("uploads/" . $edit_data['foto_sekolah'])): ?>
                    <img src="uploads/<?= $edit_data['foto_sekolah'] ?>" width="100" class="rounded border">
                    <p class="text-muted mt-1"><small>Untuk mengganti foto, gunakan form tambah baru atau hapus data ini.</small></p>
                <?php else: ?>
                    <p class="text-muted">Tidak ada foto</p>
                <?php endif; ?>
            </div>
            <button type="submit" name="update_study" class="btn btn-warning">
                <i class="fas fa-save"></i> Update Data
            </button>
            <a href="?page=studies" class="btn btn-secondary">
                <i class="fas fa-times"></i> Batal
            </a>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- FORM TAMBAH DATA (sama seperti sebelumnya) -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0"><i class="fas fa-plus-circle"></i> Tambah Riwayat Studi</h4>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Nama Sekolah <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Jenjang (Level) <span class="text-danger">*</span></label>
                    <select name="id_level" class="form-select" required>
                        <option value="">-- Pilih Level --</option>
                        <?php
                        $levels = mysqli_query($conn, "SELECT * FROM level ORDER BY id");
                        while($level = mysqli_fetch_assoc($levels)):
                        ?>
                        <option value="<?= $level['id'] ?>"><?= htmlspecialchars($level['nama']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label>Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="3"></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Tahun Lulus</label>
                    <input type="number" name="tahun_lulus" class="form-control" min="1900" max="2030">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Foto Sekolah</label>
                    <input type="file" name="foto" class="form-control" accept="image/*">
                    <small class="text-muted">Format: JPG, PNG | Maks 2MB</small>
                </div>
            </div>
            <button type="submit" name="submit_study" class="btn btn-success">
                <i class="fas fa-save"></i> Simpan
            </button>
        </form>
    </div>
</div>

<!-- TABEL DATA STUDIES -->
<div class="card">
    <div class="card-header bg-success text-white">
        <h4 class="mb-0"><i class="fas fa-table"></i> Daftar Riwayat Studi</h4>
    </div>
    <div class="card-body">
        <?php if (mysqli_num_rows($result) == 0): ?>
            <div class="alert alert-warning">Belum ada data studi.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Foto</th>
                            <th>Nama Sekolah</th>
                            <th>Jenjang</th>
                            <th>Tahun Lulus</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td>>
                                <?php if ($row['foto_sekolah'] && file_exists("uploads/" . $row['foto_sekolah'])): ?>
                                    <img src="uploads/<?= $row['foto_sekolah'] ?>" width="50" height="50" class="rounded" style="object-fit: cover;">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/50" width="50" height="50" class="rounded">
                                <?php endif; ?>
                              </td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= htmlspecialchars($row['level_nama']) ?></td>
                            <td><?= $row['tahun_lulus'] ?: '-' ?></td>
                            <td><?= htmlspecialchars(substr($row['keterangan'], 0, 50)) ?><?= strlen($row['keterangan']) > 50 ? '...' : '' ?></td>
                            <td>>
                                <a href="?page=studies&edit=<?= $row['id'] ?>" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="?page=studies&delete=<?= $row['id'] ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Yakin hapus data <?= htmlspecialchars($row['nama']) ?>?')">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>