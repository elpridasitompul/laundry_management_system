<?php
session_start();
include "koneksi.php";

if(!isset($_SESSION['login'])){
    header("Location: login.php");
    exit;
}

// Query untuk widget
$qPelanggan = oci_parse($conn,"SELECT COUNT(*) AS TOTAL FROM pelanggan");
oci_execute($qPelanggan);
$dataPelanggan = oci_fetch_array($qPelanggan, OCI_ASSOC);

$qTransaksi = oci_parse($conn,"SELECT COUNT(*) AS TOTAL FROM transaksi");
oci_execute($qTransaksi);
$dataTransaksi = oci_fetch_array($qTransaksi, OCI_ASSOC);

$qProses = oci_parse($conn,"SELECT COUNT(*) AS TOTAL FROM transaksi WHERE status != 'Selesai'");
oci_execute($qProses);
$dataProses = oci_fetch_array($qProses, OCI_ASSOC);

$qPemasukan = oci_parse($conn,"SELECT NVL(SUM(total_bayar),0) AS TOTAL FROM transaksi WHERE status != 'Batal'");
oci_execute($qPemasukan);
$dataPemasukan = oci_fetch_array($qPemasukan, OCI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Smart Laundry</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
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
        .menu a i { margin-right: 10px; font-size: 1.1rem; vertical-align: middle; }
        .menu a:hover{ background:#f3e8ff; }
        .active{ background:#e9d5ff; font-weight: 500; }

        /* CONTENT */
        .content{ margin-left:240px; padding:50px; }
        .header{ display:flex; justify-content:space-between; align-items:center; margin-bottom:40px; }
        .header h1{ font-weight:600; color:#7c3aed; }
        .admin-name { font-weight: 500; color: #6b21a8; background: #f3e8ff; padding: 8px 15px; border-radius: 10px; }

        /* CARDS */
        .cards{ display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:25px; }
        .card{ padding:25px; border-radius:20px; color:#333; box-shadow:0 5px 15px rgba(0,0,0,0.03); border: 1px solid rgba(255,255,255,0.8); transition: 0.3s; }
        .card:hover { transform: translateY(-5px); }
        .card a { text-decoration: none; color: inherit; display: block; }
        
        .card1{ background:#ede9fe; } /* lavender */
        .card2{ background:#fce7f3; } /* pink */
        .card3{ background:#e0f2fe; } /* baby blue */
        .card4{ background:#dcfce7; } /* mint */

        .card-head { display: flex; justify-content: space-between; align-items: flex-start; }
        .card-icon { font-size: 2rem; opacity: 0.5; }
        .card span{ font-size:14px; opacity:0.7; }
        .card h2{ margin-top:10px; font-size:26px; font-weight:600; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="logo">Bu Shin Laundry</div>
    <div class="menu">
        <a href="dashboard.php" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="pelanggan/index.php"><i class="bi bi-people"></i> Data Pelanggan</a>
        <a href="layanan/index.php"><i class="bi bi-list-stars"></i> Daftar Layanan</a>
        <a href="transaksi/index.php"><i class="bi bi-cart-check"></i> Transaksi Cucian</a>
        <a href="pengeluaran/index.php"><i class="bi bi-wallet2"></i> Biaya Operasional</a>
        <a href="laporan/pemasukan.php"><i class="bi bi-file-earmark-bar-graph"></i> Laporan Keuangan</a>
        <a href="logout.php" style="margin-top: 30px; color: #ef4444;"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
</div>

<div class="content">
    <div class="header">
        <h1>Dashboard</h1>
        <div class="admin-name">Admin: <?= $_SESSION['nama_admin']; ?></div>
    </div>

    <div class="cards">
        <div class="card card1">
            <a href="pelanggan/index.php">
                <div class="card-head">
                    <span>Total Pelanggan</span>
                    <i class="bi bi-person-heart card-icon"></i>
                </div>
                <h2><?= $dataPelanggan['TOTAL']; ?> Jiwa</h2>
            </a>
        </div>

        <div class="card card2">
            <a href="transaksi/index.php">
                <div class="card-head">
                    <span>Total Transaksi</span>
                    <i class="bi bi-receipt card-icon"></i>
                </div>
                <h2><?= $dataTransaksi['TOTAL']; ?> Nota</h2>
            </a>
        </div>

        <div class="card card3">
            <a href="transaksi/index.php">
                <div class="card-head">
                    <span>Sedang Diproses</span>
                    <i class="bi bi-hourglass-split card-icon"></i>
                </div>
                <h2><?= $dataProses['TOTAL']; ?> Cucian</h2>
            </a>
        </div>

        <div class="card card4">
            <a href="laporan/pemasukan.php">
                <div class="card-head">
                    <span>Pemasukan Kotor</span>
                    <i class="bi bi-cash-stack card-icon"></i>
                </div>
                <h2>Rp <?= number_format($dataPemasukan['TOTAL']); ?></h2>
            </a>
        </div>
    </div>
</div>

</body>
</html>