<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login.php");
    exit;
}

include "../koneksi.php";

// =========================
// AMBIL ID
// =========================
if(!isset($_GET['id']) || empty($_GET['id'])){
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];

// =========================
// QUERY DETAIL TRANSAKSI
// =========================
$q = oci_parse($conn, "
    SELECT
        t.tanggal_masuk,
        t.tanggal_selesai,
        t.status,
        t.status_pembayaran,
        t.metode_pembayaran,
        NVL(t.total_bayar,0) AS total_bayar,
        p.nama_pelanggan,
        p.alamat,
        p.no_hp,
        l.nama_layanan,
        l.harga_per_kg,
        dt.berat,
        dt.subtotal
    FROM transaksi t
    JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
    JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
    JOIN layanan l ON dt.id_layanan = l.id_layanan
    WHERE t.id_transaksi = :id
");

oci_bind_by_name($q, ":id", $id);
oci_execute($q);

// =========================
// AMBIL DATA PERTAMA
// =========================
// PENTING: Driver Oracle (OCI8) mengembalikan key array dalam HURUF KAPITAL
$dataAwal = oci_fetch_array($q, OCI_ASSOC);

if(!$dataAwal){
    echo "
    <script>
        alert('Data tidak ditemukan!');
        window.location='index.php';
    </script>
    ";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Transaksi - Smart Laundry</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        *{ margin:0; padding:0; box-sizing:border-box; }
        body{ font-family:'Poppins', sans-serif; background:#fafafa; color:#444; }
        .sidebar{ width:240px; height:100vh; position:fixed; left:0; top:0; padding:30px 20px; background:#fdf4ff; border-right:1px solid #f3e8ff; }
        .logo{ font-size:20px; font-weight:600; color:#a855f7; margin-bottom:40px; }
        .menu a{ display:block; padding:12px; border-radius:12px; text-decoration:none; color:#7c3aed; margin-bottom:10px; transition:0.3s; font-size:15px; }
        .menu a:hover, .menu a.active{ background:#f3e8ff; }
        .active{ background:#e9d5ff !important; font-weight:500; }
        .content{ margin-left:240px; padding:50px; width:calc(100% - 240px); }
        .header{ margin-bottom:30px; }
        .header h1{ color:#7c3aed; font-weight:600; }
        .card-detail{ background:white; border-radius:20px; padding:30px; border:1px solid #f3e8ff; box-shadow:0 5px 15px rgba(0,0,0,0.02); }
        .info-box{ background:#faf5ff; border-radius:15px; padding:20px; margin-bottom:25px; }
        .label{ font-size:13px; color:#888; margin-bottom:5px; }
        .value{ font-weight:600; color:#444; }
        .table thead{ background:#faf5ff; }
        .total-box{ background:#f3e8ff; border-radius:15px; padding:20px; margin-top:20px; }
        .btn-kembali{ background:#7c3aed; color:white; border:none; padding:10px 20px; border-radius:12px; text-decoration:none; display:inline-block; margin-top:25px; }
        
        /* Badge Status Progres & Bayar */
        .badge-custom { padding: 6px 12px; border-radius: 8px; font-weight: 600; font-size: 0.75rem; display: inline-block; }
        .st-proses { background: #fef3c7; color: #92400e; }
        .st-selesai { background: #dcfce7; color: #166534; }
        .st-diambil { background: #e0f2fe; color: #075985; }
        .bayar-lunas { background: #dcfce7; color: #166534; }
        .bayar-belum { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>

<div class="d-flex">
    <div class="sidebar">
        <div class="logo">Laundry</div>
        <div class="menu">
            <a href="../dashboard.php">Dashboard</a>
            <a href="../pelanggan/index.php">Data Pelanggan</a>
            <a href="../layanan/index.php">Daftar Layanan</a>
            <a href="index.php" class="active">Transaksi Cucian</a>
            <a href="../pengeluaran/index.php">Biaya Operasional</a>
            <a href="../laporan/pemasukan.php">Laporan Keuangan</a>
            <a href="../logout.php" style="margin-top:30px; color:#ef4444;">Logout</a>
        </div>
    </div>

    <div class="content">
        <div class="header">
            <h1>Detail Transaksi</h1>
        </div>

        <div class="card-detail">
            <div class="info-box">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="label">Nama Pelanggan</div>
                        <div class="value"><?= htmlspecialchars($dataAwal['NAMA_PELANGGAN']); ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="label">No HP</div>
                        <div class="value"><?= htmlspecialchars($dataAwal['NO_HP']); ?></div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="label">Status Laundry</div>
                        <div class="value">
                            <?php 
                            $status_laundry = strtoupper($dataAwal['STATUS']); 
                            $st_class = ($status_laundry == "SELESAI") ? "st-selesai" : (($status_laundry == "PROSES") ? "st-proses" : "st-diambil");
                            ?>
                            <span class="badge-custom <?= $st_class; ?>"><?= ucfirst(strtolower($dataAwal['STATUS'])); ?></span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="label">Status Pembayaran</div>
                        <div class="value">
                            <?php 
                            $status_bayar = !empty($dataAwal['STATUS_PEMBAYARAN']) ? strtoupper($dataAwal['STATUS_PEMBAYARAN']) : 'BELUM LUNAS'; 
                            $bayar_class = ($status_bayar == "LUNAS") ? "bayar-lunas" : "bayar-belum";
                            ?>
                            <span class="badge-custom <?= $bayar_class; ?>"><?= !empty($dataAwal['STATUS_PEMBAYARAN']) ? $dataAwal['STATUS_PEMBAYARAN'] : 'Belum Lunas'; ?></span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="label">Metode Pembayaran</div>
                        <div class="value">
                            <span class="badge bg-light text-dark border fw-medium"><?= !empty($dataAwal['METODE_PEMBAYARAN']) ? $dataAwal['METODE_PEMBAYARAN'] : '-'; ?></span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="label">Tanggal Masuk</div>
                        <div class="value">
                            <?= ($dataAwal['TANGGAL_MASUK']) ? date('d M Y', strtotime($dataAwal['TANGGAL_MASUK'])) : '-'; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle text-center">
                    <thead>
                        <tr>
                            <th width="60">No</th>
                            <th class="text-start">Layanan</th>
                            <th>Harga / Kg</th>
                            <th>Berat</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $no = 1;
                    $total = 0;
                    do {
                        $total += $dataAwal['SUBTOTAL'];
                    ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td class="text-start fw-medium"><?= htmlspecialchars($dataAwal['NAMA_LAYANAN']); ?></td>
                            <td>Rp <?= number_format($dataAwal['HARGA_PER_KG']); ?></td>
                            <td><?= $dataAwal['BERAT']; ?> Kg</td>
                            <td class="fw-semibold text-purple" style="color: #7c3aed;">Rp <?= number_format($dataAwal['SUBTOTAL']); ?></td>
                        </tr>
                    <?php
                    } while($dataAwal = oci_fetch_array($q, OCI_ASSOC));
                    ?>
                    </tbody>
                </table>
            </div>

            <div class="total-box d-flex justify-content-between align-items-center shadow-sm">
                <h4 class="mb-0 fw-bold" style="color: #7c3aed; font-size: 1.1rem;">Total Keseluruhan :</h4>
                <h4 class="mb-0 fw-bold" style="color: #7c3aed; font-size: 1.3rem;">Rp <?= number_format($total); ?></h4>
            </div>

            <a href="index.php" class="btn-kembali shadow-sm">
               <i class="bi bi-arrow-left me-1"></i> Kembali ke Transaksi
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>