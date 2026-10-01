<?php
require_once "../config/koneksi.php";

// Logika Tambah Menu
if (isset($_POST['tambah_menu'])) {
    $nama_menu = mysqli_real_escape_string($koneksi, $_POST['nama_menu']);
    $id_kategori = (int)$_POST['id_kategori'];
    $harga = (float)$_POST['harga'];

    if (!empty($nama_menu) && $id_kategori > 0 && $harga > 0) {
        mysqli_query($koneksi, "INSERT INTO menu (id_kategori, nama_menu, harga) VALUES ('$id_kategori', '$nama_menu', '$harga')");
        header("Location: menu.php?status=added");
        exit;
    }
}

// Logika Hapus Menu
if (isset($_GET['hapus'])) {
    $id_menu = (int)$_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM menu WHERE id_menu = '$id_menu'");
    header("Location: menu.php?status=deleted");
    exit;
}

// Ambil Data
$query_kategori = mysqli_query($koneksi, "SELECT * FROM kategori_menu ORDER BY nama_kategori ASC");
$query_menu = mysqli_query($koneksi, "SELECT menu.*, kategori_menu.nama_kategori 
                                      FROM menu 
                                      JOIN kategori_menu ON menu.id_kategori = kategori_menu.id_kategori 
                                      ORDER BY menu.id_menu DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Menu – Warung Mogan</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,600;12..96,800&display=swap" rel="stylesheet">
<style>
  :root {
    --ink: #1b2e2b; --ink-soft: #4a635d; --paper: #f4f7f6; --panel: #ffffff;
    --line: #d2e0db; --brand: #2d6a4f; --brand-dark: #1b4332; --radius: 14px;
    --font: "Bricolage Grotesque", sans-serif;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: var(--font); color: var(--ink); background: var(--paper); line-height: 1.6; padding: 20px; }
  .wrap { max-width: 900px; margin: 0 auto; }
  header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid var(--line); }
  h1 { font-size: 1.8rem; color: var(--brand); }
  .card-form { background: var(--panel); border: 1px solid var(--line); border-radius: var(--radius); padding: 20px; margin-bottom: 24px; }
  .form-group { margin-bottom: 12px; }
  .form-group label { display: block; font-weight: 600; margin-bottom: 4px; font-size: 0.9rem; }
  .form-control { width: 100%; padding: 10px; border: 1.5px solid var(--line); border-radius: 8px; font-family: var(--font); font-size: 0.95rem; }
  .btn-submit { background: var(--brand); color: #fff; border: none; padding: 10px 20px; border-radius: 999px; font-weight: bold; cursor: pointer; }
  table { width: 100%; border-collapse: collapse; background: var(--panel); border-radius: var(--radius); overflow: hidden; border: 1px solid var(--line); }
  th, td { padding: 12px 16px; text-align: left; border-bottom: 1px solid var(--line); font-size: 0.95rem; }
  th { background: #e8f5e9; color: var(--brand); }
  .btn-delete { color: #dc2626; text-decoration: none; font-weight: bold; }
</style>
</head>
<body>

<div class="wrap">
  <header>
    <h1>Kelola Data Menu</h1>
    <a href="index.php" style="text-decoration:none; font-weight:600; color:var(--brand);">&larr; Kembali ke Dashboard Kasir</a>
  </header>

  <!-- Form Tambah Menu -->
  <div class="card-form">
    <h3 style="margin-bottom: 12px; color: var(--brand);">Tambah Menu Baru</h3>
    <form action="menu.php" method="POST">
      <div class="form-group">
        <label>Nama Menu Vegetarian</label>
        <input type="text" name="nama_menu" class="form-control" placeholder="Contoh: Bakmie Vegetarian" required>
      </div>
      <div class="form-group">
        <label>Kategori</label>
        <select name="id_kategori" class="form-control" required>
          <option value="">-- Pilih Kategori --</option>
          <?php while($cat = mysqli_fetch_assoc($query_kategori)): ?>
            <option value="<?= $cat['id_kategori'] ?>"><?= htmlspecialchars($cat['nama_kategori']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Harga (Rp)</label>
        <input type="number" name="harga" class="form-control" placeholder="25000" required>
      </div>
      <button type="submit" name="tambah_menu" class="btn-submit">Simpan Menu</button>
    </form>
  </div>

  <!-- Tabel Daftar Menu -->
  <h3>Daftar Menu Saat Ini</h3>
  <table style="margin-top: 12px;">
    <thead>
      <tr>
        <th>No</th>
        <th>Nama Menu</th>
        <th>Kategori</th>
        <th>Harga</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if(mysqli_num_rows($query_menu) > 0): ?>
        <?php $no = 1; while($m = mysqli_fetch_assoc($query_menu)): ?>
          <tr>
            <td><?= $no++ ?></td>
            <td><strong><?= htmlspecialchars($m['nama_menu']) ?></strong></td>
            <td><?= htmlspecialchars($m['nama_kategori']) ?></td>
            <td>Rp <?= number_format($m['harga'], 0, ',', '.') ?></td>
            <td>
              <a href="menu.php?hapus=<?= $m['id_menu'] ?>" class="btn-delete" onclick="return confirm('Hapus menu ini?')">Hapus</a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="5">Belum ada data menu.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

</body>
</html>