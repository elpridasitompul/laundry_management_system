<?php
session_start();
include "../koneksi.php";

if(!isset($_SESSION['login'])){
    header("Location: ../login.php");
    exit;
}

// Query pastikan mengambil dari tabel layanan
$q = oci_parse($conn, "SELECT * FROM layanan ORDER BY id_layanan DESC");
oci_execute($q);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daftar Layanan - Smart Laundry</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *{ margin:0; padding:0; box-sizing:border-box; }
        body{ font-family:'Poppins', sans-serif; background:#fafafa; color:#444; overflow-x:hidden; }

        /* SIDEBAR */
        .sidebar{
            width:240px; height:100vh; position:fixed; left:0; top:0;
            padding:30px 20px; background:#fdf4ff; border-right:1px solid #f3e8ff;
        }
        .logo{ font-size:20px; font-weight:600; color:#a855f7; margin-bottom:40px; }
        .menu a{
            display:block; padding:12px; border-radius:12px; text-decoration:none;
            color:#7c3aed; margin-bottom:10px; transition:0.3s; font-size: 15px;
        }
        .menu a:hover{ background:#f3e8ff; }
        .active{ background:#e9d5ff; font-weight: 500; }

        /* CONTENT */
        .content{ margin-left:240px; padding:50px; }
        .header{ display:flex; justify-content:space-between; align-items:center; margin-bottom:40px; }
        .header h1{ font-weight:600; color:#7c3aed; }

        .btn-tambah{
            padding:12px 24px; background:#7c3aed; color:white;
            text-decoration:none; border-radius:12px; font-weight:500;
        }

        .table-container{
            background:white; padding:30px; border-radius:20px;
            box-shadow:0 5px 15px rgba(0,0,0,0.02); border:1px solid #f3e8ff;
        }

        table{ width:100%; border-collapse:collapse; }
        th{ text-align:left; padding:15px; border-bottom:2px solid #f3e8ff; color:#a855f7; }
        td{ padding:15px; border-bottom:1px solid #f9fafb; font-size:14px; }

        .price-tag{
            color:#7c3aed; font-weight:600; background:#f3e8ff;
            padding:5px 12px; border-radius:8px;
        }

        .btn-edit{ color:#f59e0b; text-decoration:none; margin-right:15px; }
        .btn-hapus{ color:#ef4444; text-decoration:none; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="logo">Laundry</div>
    <div class="menu">
        <a href="../dashboard.php">Dashboard</a>
        <a href="../pelanggan/index.php">Data Pelanggan</a>
        <a href="index.php" class="active">Daftar Layanan</a>
        <a href="../transaksi/index.php">Transaksi Cucian</a>
        <a href="../pengeluaran/index.php">Biaya Operasional</a>
        <a href="../laporan/pemasukan.php">Laporan Keuangan</a>
        <a href="../logout.php" style="margin-top: 30px; color: #ef4444;">Logout</a>
    </div>
</div>

<div class="content">
    <div class="header">
        <h1>Daftar Layanan</h1>
        <a href="tambah.php" class="btn-tambah">+ Layanan Baru</a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th width="60">No</th>
                    <th>Nama Layanan</th>
                    <th>Harga / Kg</th>
                    <th style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                while($row = oci_fetch_array($q, OCI_ASSOC)){
                    // Kita paksa semua key jadi huruf kecil agar pemanggilan lebih mudah
                    $d = array_change_key_case($row, CASE_LOWER);
                ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td style="font-weight:500;"><?= $d['nama_layanan']; ?></td>
                    <td><span class="price-tag">Rp <?= number_format($d['harga_per_kg']); ?></span></td>
                    <td style="text-align:center;">
                        <a href="edit.php?id=<?= $d['id_layanan']; ?>" class="btn-edit">Edit</a>
                        <a href="hapus.php?id=<?= $d['id_layanan']; ?>" class="btn-hapus" onclick="return confirm('Hapus?')">Hapus</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>