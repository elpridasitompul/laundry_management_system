<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

// Query mengambil data pengeluaran
$q = oci_parse($conn,"SELECT * FROM pengeluaran ORDER BY id_pengeluaran DESC");
oci_execute($q);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Biaya Operasional - Smart Laundry</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<div class="d-flex">
    <div class="sidebar">
        <div class="logo">Laundry</div>
        <div class="menu">
            <a href="../dashboard.php">Dashboard</a>
            <a href="index.php">Data Pelanggan</a>
            <a href="../layanan/index.php">Daftar Layanan</a>
            <a href="../transaksi/index.php">Transaksi Cucian</a>
            <a href="../pengeluaran/index.php" class="active">Biaya Operasional</a>
            <a href="../laporan/pemasukan.php">Laporan Keuangan</a>
            <a href="../logout.php" style="margin-top: 30px; color: #ef4444;">Logout</a>
        </div>
    </div>

    <div class="content">
        <div class="header">
            <h1>Biaya Operasional</h1>
            <a href="tambah.php" class="btn-tambah shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Tambah Pengeluaran
            </a>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th width="60" class="text-center">No</th>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Jumlah Biaya</th>
                            <th width="100" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $no = 1;
                    while($d = oci_fetch_array($q, OCI_ASSOC)){
                    ?>
                        <tr>
                            <td class="text-center"><?= $no++; ?></td>
                            <td style="color: #6b7280;"><?= date('d M Y', strtotime($d['TANGGAL'])); ?></td>
                            <td style="font-weight:500; color:#333;"><?= $d['KETERANGAN']; ?></td>
                            <td>
                                <span class="jumlah-tag">
                                    Rp <?= number_format($d['JUMLAH']); ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="hapus.php?id=<?= $d['ID_PENGELUARAN']; ?>" 
                                   class="btn-hapus" 
                                   onclick="return confirm('Yakin ingin menghapus data pengeluaran ini?')">
                                    <i class="bi bi-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>