<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

$data_laporan = [];
$total_masuk = 0;
$total_keluar = 0;

// 1. QUERY MENGAMBIL DATA PEMASUKAN
$sql_masuk = "SELECT 'PEMASUKAN' AS JENIS,
                     'Pendapatan - ' || p.nama_pelanggan AS KETERANGAN,
                     t.METODE_PEMBAYARAN AS METODE,
                     NVL(t.total_bayar, 0) AS NOMINAL,
                     t.id_transaksi AS SORT_ID
              FROM transaksi t
              JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
              WHERE UPPER(t.STATUS_PEMBAYARAN) = 'LUNAS'";

$q_masuk = oci_parse($conn, $sql_masuk);
$eksekusi_masuk = @oci_execute($q_masuk);

if($eksekusi_masuk){
    while($row = oci_fetch_array($q_masuk, OCI_ASSOC)){
        $data_laporan[] = $row;
        $total_masuk += $row['NOMINAL'];
    }
}

// 2. QUERY MENGAMBIL DATA PENGELUARAN
$sql_keluar = "SELECT * FROM pengeluaran";
$q_keluar = oci_parse($conn, $sql_keluar);
$eksekusi_keluar = @oci_execute($q_keluar);

if($eksekusi_keluar){
    while($row = oci_fetch_array($q_keluar, OCI_ASSOC)){
        $nominal_pengeluaran = 0;
        $keterangan_pengeluaran = 'Biaya Operasional';

        foreach($row as $key => $val){
            $key_upper = strtoupper($key);
            if(in_array($key_upper, ['NOMINAL', 'JUMLAH', 'TOTAL', 'BIAYA', 'HARGA'])){
                $nominal_pengeluaran = floatval($val);
            }
            if(in_array($key_upper, ['KETERANGAN', 'DESKRIPSI', 'NAMA_PENGELUARAN', 'KEPERLUAN'])){
                $keterangan_pengeluaran = $val;
            }
        }

        $sort_id = isset($row['ID_PENGELUARAN']) ? $row['ID_PENGELUARAN'] : 0;

        $data_laporan[] = [
            'JENIS' => 'PENGELUARAN',
            'KETERANGAN' => $keterangan_pengeluaran,
            'METODE' => 'Tunai',
            'NOMINAL' => $nominal_pengeluaran,
            'SORT_ID' => $sort_id
        ];
        $total_keluar += $nominal_pengeluaran;
    }
}

// Urutkan data berdasarkan ID terbaru
usort($data_laporan, function($a, $b) {
    return $b['SORT_ID'] <=> $a['SORT_ID'];
});

$laba_bersih = $total_masuk - $total_keluar;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan - Smart Laundry</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        *{ margin:0; padding:0; box-sizing:border-box; }
        body{ font-family:'Plus Jakarta Sans', sans-serif; background:#f8fafc; color:#334155; }
        
        /* SIDEBAR STYLES */
        .sidebar{ width:260px; height:100vh; position:fixed; left:0; top:0; padding:40px 24px; background:#fff; border-right:1px solid #f1f5f9; }
        .logo{ font-size:22px; font-weight:700; color:#7c3aed; margin-bottom:40px; display: flex; align-items: center; gap: 10px; }
        .menu a{ display:block; padding:12px 16px; border-radius:12px; text-decoration:none; color:#64748b; margin-bottom:8px; transition:all 0.2s ease; font-size: 14px; font-weight: 500; }
        .menu a:hover{ background:#f8f5ff; color:#7c3aed; }
        .menu a.active{ background:#f3e8ff; color:#7c3aed; font-weight: 600; }
        
        /* CONTENT STYLES */
        .content{ margin-left:260px; padding:50px; width: calc(100% - 260px); }
        .header h1{ font-weight:700; color:#0f172a; font-size: 26px; margin: 0; }
        .btn-print-trigger { border-radius: 12px; padding: 10px 20px; font-weight: 600; font-size: 14px; border: 1px solid #e2e8f0; transition: all 0.2s; background: white; color: #334155; }
        .btn-print-trigger:hover { background: #0f172a; color: white; border-color: #0f172a; }
        
        /* CARDS RINGKASAN ATAS */
        .card-stat { border-radius: 20px; padding: 24px; margin-bottom: 30px; border: none; }
        .card-stat h3 { font-size: 24px; font-weight: 700; margin-top: 6px; margin-bottom: 0; letter-spacing: -0.5px; }
        .card-stat .title-sub { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.8; }
        .bg-pemasukan { background: #fff; color: #0f172a; border: 1px solid #e2e8f0; }
        .bg-pemasukan h3 { color: #10b981; }
        .bg-pengeluaran { background: #fff; color: #0f172a; border: 1px solid #e2e8f0; }
        .bg-pengeluaran h3 { color: #f43f5e; }
        .bg-laba { background: #7c3aed; color: white; box-shadow: 0 10px 25px rgba(124, 58, 237, 0.15); }
        
        /* TABLE STYLES */
        .table-container{ background:white; padding:30px; border-radius:24px; border:1px solid #e2e8f0; margin-bottom: 30px; }
        .table thead th { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; padding: 16px; border-bottom: 1px solid #e2e8f0; background: #fafafa; }
        .table tbody td { padding: 16px; font-size: 14px; border-bottom: 1px solid #f1f5f9; color: #334155; }
        .table tbody tr:hover { background-color: #f8fafc; }
        
        /* BADGES */
        .badge-masuk { background: #dcfce7; color: #15803d; padding: 4px 12px; border-radius: 8px; font-weight: 600; font-size: 11px; }
        .badge-keluar { background: #fee2e2; color: #b91c1c; padding: 4px 12px; border-radius: 8px; font-weight: 600; font-size: 11px; }
        
        /* LOWER SUMMARY LAYOUT */
        .summary-box { background: #fff; padding: 30px; border-radius: 24px; border: 1px solid #e2e8f0; }
        .summary-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; color: #64748b; font-weight: 500; }
        .summary-row .val-style { font-weight: 600; color: #0f172a; }
        .summary-total { display: flex; justify-content: space-between; padding-top: 16px; margin-top: 12px; border-top: 2px dashed #e2e8f0; font-size: 16px; font-weight: 700; color: #7c3aed; }
        .summary-total .val-total { font-size: 18px; }

        @media print {
            .sidebar, .btn-print-trigger { display: none !important; }
            .content { margin-left: 0 !important; width: 100% !important; padding: 20px !important; }
            .table-container, .summary-box { border: none !important; box-shadow: none !important; padding: 0 !important; }
        }
    </style>
</head>
<body>

<div class="d-flex">
    <div class="sidebar">
        <div class="logo"><i class="bi bi-water text-primary me-2"></i>Laundry</div>
        <div class="menu">
            <a href="../dashboard.php">Dashboard</a>
            <a href="../pelanggan/index.php">Data Pelanggan</a>
            <a href="../layanan/index.php">Daftar Layanan</a>
            <a href="../transaksi/index.php">Transaksi Cucian</a>
            <a href="../pengeluaran/index.php">Biaya Operasional</a>
            <a href="pemasukan.php" class="active">Laporan Keuangan</a>
            <a href="../logout.php" style="margin-top: 40px; color: #ef4444;"><i class="bi bi-box-arrow-left me-2"></i>Logout</a>
        </div>
    </div>

    <div class="content">
        <div class="header d-flex justify-content-between align-items-center mb-4">
            <h1>Laporan Keuangan & Laba Rugi</h1>
            <button onclick="window.print()" class="btn btn-print-trigger">
                <i class="bi bi-printer me-2"></i>Cetak Laporan / PDF
            </button>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <div class="card card-stat bg-pemasukan">
                    <span class="title-sub">Total Pemasukan (Lunas)</span>
                    <h3>Rp <?= number_format($total_masuk); ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stat bg-pengeluaran">
                    <span class="title-sub">Total Pengeluaran</span>
                    <h3>Rp <?= number_format($total_keluar); ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stat bg-laba">
                    <span class="title-sub">Laba Bersih Bersih</span>
                    <h3>Rp <?= number_format($laba_bersih); ?></h3>
                </div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table align-middle m-0">
                    <thead>
                        <tr>
                            <th width="60" class="text-center">No</th>
                            <th width="150" class="text-center">Jenis Kas</th>
                            <th class="text-start">Keterangan / Deskripsi</th>
                            <th width="140" class="text-center">Metode</th>
                            <th width="160" class="text-end">Pemasukan (+)</th>
                            <th width="160" class="text-end">Pengeluaran (-)</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    if(empty($data_laporan)){
                        echo "<tr><td colspan='6' class='text-muted py-5 text-center'>Belum ada data keuangan tercatat.</td></tr>";
                    } else {
                        $no = 1;
                        foreach($data_laporan as $d){
                            $is_masuk = ($d['JENIS'] == 'PEMASUKAN');
                        ?>
                            <tr>
                                <td class="text-center text-secondary"><?= $no++; ?></td>
                                <td class="text-center">
                                    <span class="<?= $is_masuk ? 'badge-masuk' : 'badge-keluar'; ?>">
                                        <?= $d['JENIS']; ?>
                                    </span>
                                </td>
                                <td class="text-start fw-medium" style="color: #0f172a;">
                                    <?= htmlspecialchars($d['KETERANGAN']); ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-secondary border px-3 py-2 fw-normal" style="border-radius: 8px;">
                                        <?= htmlspecialchars($d['METODE']); ?>
                                    </span>
                                </td>
                                
                                <td class="text-end fw-bold text-success">
                                    <?= $is_masuk ? 'Rp ' . number_format($d['NOMINAL']) : '-'; ?>
                                </td>
                                
                                <td class="text-end fw-bold text-danger">
                                    <?= !$is_masuk ? 'Rp ' . number_format($d['NOMINAL']) : '-'; ?>
                                </td>
                            </tr>
                        <?php 
                        } 
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if(!empty($data_laporan)): ?>
        <div class="row g-4">
            <div class="col-md-7">
                <div class="p-4 bg-light rounded-4 border text-secondary" style="font-size: 13px;">
                    <h6 class="fw-bold text-dark mb-2" style="font-size: 14px;"><i class="bi bi-info-circle-fill text-primary me-2"></i>Catatan Pengelola Toko</h6>
                    <ul class="m-0 ps-3">
                        <li>Laporan kas ini digenerate secara otomatis berdasarkan status data riil.</li>
                        <li>Pemasukan yang dihitung hanya bersumber dari transaksi dengan status <b>Lunas</b>.</li>
                        <li>Gunakan opsi tombol cetak di bagian atas untuk menyimpan laporan dalam format berkas PDF resmi.</li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-5">
                <div class="summary-box">
                    <div class="summary-row">
                        <span>Sub Total Pemasukan:</span>
                        <span class="val-style text-success">Rp <?= number_format($total_masuk); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Sub Total Pengeluaran:</span>
                        <span class="val-style text-danger">Rp <?= number_format($total_keluar); ?></span>
                    </div>
                    <div class="summary-total">
                        <span>TOTAL LABA BERSIH:</span>
                        <span class="val-total fw-bold">Rp <?= number_format($laba_bersih); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>