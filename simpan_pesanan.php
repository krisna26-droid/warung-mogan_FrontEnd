<?php
require_once "config/koneksi.php";

header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || empty($data['items'])) {
    echo json_encode(['success' => false, 'message' => 'Keranjang pesanan kosong.']);
    exit;
}

$lokasi_meja = isset($data['meja']) ? mysqli_real_escape_string($koneksi, $data['meja']) : '01';
$items = $data['items'];
$subtotal = 0;

// Cek / Buat ID QR Code
$query_qr = mysqli_query($koneksi, "SELECT id_qr_code FROM qr_code WHERE lokasi_meja = '$lokasi_meja' LIMIT 1");
if (mysqli_num_rows($query_qr) > 0) {
    $row_qr = mysqli_fetch_assoc($query_qr);
    $id_qr_code = $row_qr['id_qr_code'];
} else {
    mysqli_query($koneksi, "INSERT INTO qr_code (kode_qr, lokasi_meja) VALUES ('QR-M$lokasi_meja', '$lokasi_meja')");
    $id_qr_code = mysqli_insert_id($koneksi);
}

// Simpan Keranjang
$query_keranjang = mysqli_query($koneksi, "INSERT INTO keranjang (id_qr_code) VALUES ('$id_qr_code')");
if (!$query_keranjang) {
    echo json_encode(['success' => false, 'message' => 'Gagal membuat pesanan.']);
    exit;
}

$id_keranjang = mysqli_insert_id($koneksi);

// Simpan Detail Pesanan + Catatan Khusus
foreach ($items as $item) {
    $id_menu = (int)$item['id'];
    $nama_pesanan = mysqli_real_escape_string($koneksi, $item['nama']);
    $jumlah = (int)$item['jumlah'];
    $harga_satuan = (float)$item['harga'];
    $catatan = isset($item['catatan']) ? mysqli_real_escape_string($koneksi, $item['catatan']) : '';
    $subtotal += ($harga_satuan * $jumlah);

    mysqli_query($koneksi, "INSERT INTO detail_keranjang (id_keranjang, id_menu, nama_pesanan, jumlah, harga_satuan, catatan) 
                            VALUES ('$id_keranjang', '$id_menu', '$nama_pesanan', '$jumlah', '$harga_satuan', '$catatan')");
}

$ppn = $subtotal * 0.10;
$total_pembelian = $subtotal + $ppn;

// Simpan Pembayaran
mysqli_query($koneksi, "INSERT INTO pembayaran (id_keranjang, total_pembelian) VALUES ('$id_keranjang', '$total_pembelian')");

echo json_encode([
    'success' => true,
    'message' => 'Pesanan berhasil disimpan! Silakan lakukan pembayaran di kasir.',
    'id_keranjang' => $id_keranjang,
    'total' => $total_pembelian
]);
?>