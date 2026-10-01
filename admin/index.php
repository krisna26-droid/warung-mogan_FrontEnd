<?php
require_once "../config/koneksi.php";

// Ambil data transaksi pesanan yang masuk
$query_pesanan = mysqli_query($koneksi, "
    SELECT 
        pembayaran.id_pembayaran,
        pembayaran.total_pembelian,
        keranjang.id_keranjang,
        keranjang.tanggal_dibuat,
        qr_code.lokasi_meja
    FROM pembayaran
    JOIN keranjang ON pembayaran.id_keranjang = keranjang.id_keranjang
    JOIN qr_code ON keranjang.id_qr_code = qr_code.id_qr_code
    ORDER BY keranjang.id_keranjang DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Kasir – Warung Mogan</title>
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
  .wrap { max-width: 1000px; margin: 0 auto; }
  header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid var(--line); }
  h1 { font-size: 1.8rem; color: var(--brand); }
  nav a { text-decoration: none; font-weight: 600; color: var(--brand); margin-left: 16px; }
  .grid-orders { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; }
  .card-order { background: var(--panel); border: 1px solid var(--line); border-radius: var(--radius); padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
  .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid var(--line); padding-bottom: 8px; }
  .badge-meja { background: var(--brand); color: #fff; padding: 4px 12px; border-radius: 999px; font-weight: bold; font-size: 0.9rem; }
  .order-item { font-size: 0.9rem; margin-bottom: 8px; border-bottom: 1px dashed var(--line); padding-bottom: 6px; }
  .order-item-row { display: flex; justify-content: space-between; }
  .order-notes { font-size: 0.8rem; color: #d97706; font-style: italic; font-weight: 600; }
  .order-total { margin-top: 12px; border-top: 2px dashed var(--line); padding-top: 10px; display: flex; justify-content: space-between; font-weight: bold; font-size: 1.05rem; color: var(--brand); }
  .btn-confirm { display: block; width: 100%; text-align: center; background: var(--brand); color: #fff; text-decoration: none; padding: 10px; border-radius: 999px; font-weight: bold; margin-top: 14px; transition: 0.2s; }
  .btn-confirm:hover { background: var(--brand-dark); }
</style>
</head>
<body>

<div class="wrap">
  <header>
    <h1>Dashboard Kasir / Admin</h1>
    <div>
      <a href="menu.php">Kelola Menu & Kategori</a>
      <a href="../index.php">&larr; Web Utama</a>
    </div>
  </header>

  <h2 style="margin-bottom: 16px; font-size: 1.2rem;">Daftar Pesanan Masuk</h2>

  <div class="grid-orders">
    <?php if(mysqli_num_rows($query_pesanan) > 0): ?>
      <?php while($order = mysqli_fetch_assoc($query_pesanan)): ?>
        <?php 
          $id_keranjang = $order['id_keranjang'];
          $query_detail = mysqli_query($koneksi, "SELECT * FROM detail_keranjang WHERE id_keranjang = '$id_keranjang'");
        ?>
        <div class="card-order">
          <div class="card-header">
            <span class="badge-meja">Meja #<?= htmlspecialchars($order['lokasi_meja']) ?></span>
            <small style="color:var(--ink-soft);"><?= date('H:i, d M Y', strtotime($order['tanggal_dibuat'])) ?></small>
          </div>

          <div class="order-items-list">
            <?php while($item = mysqli_fetch_assoc($query_detail)): ?>
              <div class="order-item">
                <div class="order-item-row">
                  <span><?= htmlspecialchars($item['nama_pesanan']) ?> (x<?= $item['jumlah'] ?>)</span>
                  <span>Rp <?= number_format($item['harga_satuan'] * $item['jumlah'], 0, ',', '.') ?></span>
                </div>
                <?php if(!empty($item['catatan'])): ?>
                  <div class="order-notes">Ket: "<?= htmlspecialchars($item['catatan']) ?>"</div>
                <?php endif; ?>
              </div>
            <?php endwhile; ?>
          </div>

          <div class="order-total">
            <span>Total (+PPN):</span>
            <span>Rp <?= number_format($order['total_pembelian'], 0, ',', '.') ?></span>
          </div>

          <!-- Mengarah ke konfirmasi.php tanpa awalan admin/ -->
          <a href="konfirmasi.php?id_pembayaran=<?= $order['id_pembayaran'] ?>" class="btn-confirm" onclick="return confirm('Konfirmasi pembayaran untuk Meja #<?= $order['lokasi_meja'] ?>?')">Konfirmasi Pembayaran</a>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p style="color: var(--ink-soft);">Belum ada pesanan masuk saat ini.</p>
    <?php endif; ?>
  </div>
</div>

</body>
</html>