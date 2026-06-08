<?php
session_start();
include "../koneksi.php";

if(!isset($_SESSION['login'])){
    header("Location: ../login.php");
    exit;
}

// Ambil data pelanggan
$q = oci_parse($conn, "SELECT * FROM pelanggan ORDER BY id_pelanggan DESC");
oci_execute($q);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Pelanggan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *{ margin:0; padding:0; box-sizing:border-box; }
        body{ font-family:'Poppins', sans-serif; background:#fafafa; color:#444; overflow-x:hidden; }

        /* SIDEBAR (Sesuai Dashboard) */
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
        
        .header{
            display:flex; justify-content:space-between; align-items:center; margin-bottom:40px;
        }
        .header h1{ font-weight:600; color:#7c3aed; }

        /* BUTTON TAMBAH */
        .btn-tambah{
            padding:12px 24px; background:#7c3aed; color:white;
            text-decoration:none; border-radius:12px; font-weight:500;
            transition:0.3s; display:inline-block;
        }
        .btn-tambah:hover{ background:#6d28d9; box-shadow:0 4px 12px rgba(124,58,237,0.2); }

        /* TABLE CONTAINER */
        .table-container{
            background:white; padding:30px; border-radius:20px;
            box-shadow:0 5px 15px rgba(0,0,0,0.02); border:1px solid #f3e8ff;
        }

        table{ width:100%; border-collapse:collapse; }
        th{ 
            text-align:left; padding:15px; border-bottom:2px solid #f3e8ff; 
            color:#a855f7; font-weight:600; font-size: 14px;
        }
        td{ padding:15px; border-bottom:1px solid #f9fafb; font-size:14px; color:#555; }
        tr:hover{ background:#fdfaff; }

        /* ACTIONS */
        .btn-edit{ color:#f59e0b; text-decoration:none; margin-right:15px; font-weight:500; }
        .btn-hapus{ color:#ef4444; text-decoration:none; font-weight:500; }
        
        .badge-wa{
            background:#dcfce7; color:#166534; padding:5px 10px; 
            border-radius:8px; font-size:12px; font-weight:500;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="logo">Laundry</div>
    <div class="menu">
        <a href="../dashboard.php">Dashboard</a>
        <a href="index.php" class="active">Data Pelanggan</a>
        <a href="../layanan/index.php">Daftar Layanan</a>
        <a href="../transaksi/index.php">Transaksi Cucian</a>
        <a href="../pengeluaran/index.php">Biaya Operasional</a>
        <a href="../laporan/pemasukan.php">Laporan Keuangan</a>
        <a href="../logout.php" style="margin-top: 30px; color: #ef4444;">Logout</a>
    </div>
</div>

<div class="content">
    <div class="header">
        <h1>Data Pelanggan</h1>
        <a href="tambah.php" class="btn-tambah">+ Tambah Pelanggan</a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nama Pelanggan</th>
                    <th>Alamat</th>
                    <th>No. WhatsApp</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                while($d = oci_fetch_array($q, OCI_ASSOC)){
                ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td style="font-weight:500; color:#333;"><?= $d['NAMA_PELANGGAN']; ?></td>
                    <td><?= $d['ALAMAT']; ?></td>
                    <td><span class="badge-wa"><?= $d['NO_HP']; ?></span></td>
                    <td style="text-align: center;">
                        <a href="edit.php?id=<?= $d['ID_PELANGGAN']; ?>" class="btn-edit">Edit</a>
                        <a href="hapus.php?id=<?= $d['ID_PELANGGAN']; ?>" class="btn-hapus" onclick="return confirm('Hapus pelanggan ini?')">Hapus</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>