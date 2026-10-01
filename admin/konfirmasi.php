<?php
require_once "../config/koneksi.php";

if (isset($_GET['id_pembayaran'])) {
    $id_pembayaran = (int)$_GET['id_pembayaran'];

    // 1. Ambil id_keranjang
    $query_get = mysqli_query($koneksi, "SELECT id_keranjang FROM pembayaran WHERE id_pembayaran = '$id_pembayaran'");
    if (mysqli_num_rows($query_get) > 0) {
        $row = mysqli_fetch_assoc($query_get);
        $id_keranjang = $row['id_keranjang'];

        // 2. Hapus data pesanan
        mysqli_query($koneksi, "DELETE FROM pembayaran WHERE id_pembayaran = '$id_pembayaran'");
        mysqli_query($koneksi, "DELETE FROM detail_keranjang WHERE id_keranjang = '$id_keranjang'");
        mysqli_query($koneksi, "DELETE FROM keranjang WHERE id_keranjang = '$id_keranjang'");
    }

    header("Location: index.php?status=success");
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>