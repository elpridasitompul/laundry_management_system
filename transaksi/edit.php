<?php
session_start();
include "../koneksi.php";

$id = $_GET['id'];

// 1. QUERY AMBIL DATA (Gunakan Huruf Besar agar sesuai Oracle)
$q = oci_parse($conn, "
    SELECT t.ID_TRANSAKSI, t.STATUS, t.TOTAL_BAYAR, t.METODE_PEMBAYARAN, p.NAMA_PELANGGAN 
    FROM transaksi t 
    JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan 
    WHERE t.id_transaksi = :id
");
oci_bind_by_name($q, ":id", $id);
oci_execute($q);
$d = oci_fetch_array($q, OCI_ASSOC);

// 2. LOGIKA UPDATE
if(isset($_POST['update'])){
    $status = $_POST['status'];
    $total = $_POST['total_bayar'];
    $metode = $_POST['metode_pembayaran'];

    $u = "UPDATE transaksi SET status = :status, total_bayar = :total, metode_pembayaran = :metode WHERE id_transaksi = :id";
    $s = oci_parse($conn, $u);
    oci_bind_by_name($s, ":status", $status);
    oci_bind_by_name($s, ":total", $total);
    oci_bind_by_name($s, ":metode", $metode);
    oci_bind_by_name($s, ":id", $id);

    if(oci_execute($s, OCI_COMMIT_ON_SUCCESS)){
        header("Location: index.php");
        exit;
    }
}

// Tambahkan pengecekan isset agar tidak error jika data kosong
$metode_saat_ini = isset($d['METODE_PEMBAYARAN']) ? $d['METODE_PEMBAYARAN'] : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Transaksi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .card-edit { border-radius: 15px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="d-flex align-items-center" style="min-height: 100vh;">
<div class="container">
    <div class="card card-edit mx-auto" style="max-width: 500px;">
        <div class="card-body p-4">
            <h4 class="mb-4 fw-bold text-primary">Update Transaksi</h4>
            <p class="text-muted mb-4">Pelanggan: <strong><?= $d['NAMA_PELANGGAN']; ?></strong></p>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Total Bayar (Rp)</label>
                    <input type="number" name="total_bayar" class="form-control" value="<?= $d['TOTAL_BAYAR']; ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Metode Pembayaran</label>
                    <select name="metode_pembayaran" class="form-select">
                        <option value="" disabled <?= ($metode_saat_ini == '') ? 'selected' : ''; ?>>-- Pilih Metode --</option>
                        <option value="Tunai" <?= ($metode_saat_ini == 'Tunai') ? 'selected' : ''; ?>>Tunai / COD</option>
                        <option value="Transfer" <?= ($metode_saat_ini == 'Transfer') ? 'selected' : ''; ?>>Transfer Bank</option>
                        <option value="DANA" <?= ($metode_saat_ini == 'DANA') ? 'selected' : ''; ?>>E-Wallet (DANA/OVO)</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label">Status Progres</label>
                    <select name="status" class="form-select">
                        <option value="Proses" <?= ($d['STATUS'] == 'Proses') ? 'selected' : ''; ?>>Proses</option>
                        <option value="Selesai" <?= ($d['STATUS'] == 'Selesai') ? 'selected' : ''; ?>>Selesai (Siap Ambil)</option>
                        <option value="Diambil" <?= ($d['STATUS'] == 'Diambil') ? 'selected' : ''; ?>>Diambil / Selesai</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <a href="index.php" class="btn btn-light w-50" style="border-radius: 10px;">Batal</a>
                    <button type="submit" name="update" class="btn btn-primary w-50" style="border-radius: 10px;">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>