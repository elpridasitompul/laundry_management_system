<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: ../login.php");
    exit;
}
include "../koneksi.php";

// Konfigurasi Bank / E-Wallet
$nama_bank = "Bank Mandiri";
$norek_bank = "123-456-7890 a/n Elprida Sitompul";

// 1. UPDATE STATUS PROGRES
if(isset($_GET['action']) && $_GET['action'] == 'update' && isset($_GET['id'])){
    $id_upd = $_GET['id'];
    $st_upd = $_GET['status'];
    $sql_upd = "UPDATE transaksi SET status = :st WHERE id_transaksi = :id";
    $parse_upd = oci_parse($conn, $sql_upd);
    oci_bind_by_name($parse_upd, ":st", $st_upd);
    oci_bind_by_name($parse_upd, ":id", $id_upd);
    oci_execute($parse_upd, OCI_COMMIT_ON_SUCCESS);
    header("Location: index.php");
    exit;
}

// 2. LOGIKA KONFIRMASI (LOCK DATA) - Validasi Ganda: Progres Wajib Selesai & Bayar Wajib Lunas
if(isset($_GET['action']) && $_GET['action'] == 'konfirmasi' && isset($_GET['id'])){
    $id_konf = $_GET['id'];
    
    // Validasi PHP: Cek status progres dan status pembayaran di DB
    $cek = oci_parse($conn, "SELECT status, status_pembayaran FROM transaksi WHERE id_transaksi = :id");
    oci_bind_by_name($cek, ":id", $id_konf);
    oci_execute($cek);
    $row_cek = oci_fetch_array($cek, OCI_ASSOC);
    
    $st_progres = !empty($row_cek['STATUS']) ? strtoupper($row_cek['STATUS']) : 'PROSES';
    $st_bayar = !empty($row_cek['STATUS_PEMBAYARAN']) ? strtoupper($row_cek['STATUS_PEMBAYARAN']) : 'BELUM LUNAS';
    
    if($st_progres !== 'SELESAI' || $st_bayar !== 'LUNAS') {
        header("Location: index.php?msg=error_status");
    } else {
        $sql_konf = "UPDATE transaksi SET konfirmasi_admin = 1 WHERE id_transaksi = :id";
        $parse_konf = oci_parse($conn, $sql_konf);
        oci_bind_by_name($parse_konf, ":id", $id_konf);
        oci_execute($parse_konf, OCI_COMMIT_ON_SUCCESS);
        header("Location: index.php");
    }
    exit;
}

// 3. UPDATE STATUS PEMBAYARAN
if(isset($_GET['action']) && $_GET['action'] == 'update_bayar' && isset($_GET['id'])){
    $id_bayar = $_GET['id'];
    $st_bayar = $_GET['status_bayar'];
    $sql_bayar = "UPDATE transaksi SET status_pembayaran = :st WHERE id_transaksi = :id";
    $parse_bayar = oci_parse($conn, $sql_bayar);
    oci_bind_by_name($parse_bayar, ":st", $st_bayar);
    oci_bind_by_name($parse_bayar, ":id", $id_bayar);
    oci_execute($parse_bayar, OCI_COMMIT_ON_SUCCESS);
    header("Location: index.php");
    exit;
}

function formatWhatsApp($nomor) {
    $nomor = preg_replace('/[^0-9]/', '', $nomor);
    if (substr($nomor, 0, 1) === '0') {
        $nomor = '62' . substr($nomor, 1);
    } elseif (substr($nomor, 0, 2) !== '62') {
        $nomor = '62' . $nomor;
    }
    return $nomor;
}

// Query Ambil Data (DIKEMBALIKAN KE VERSI AMAN YANG SUDAH SESUAI DATABASE)
$q = oci_parse($conn,"
    SELECT t.id_transaksi, t.status, t.METODE_PEMBAYARAN, t.STATUS_PEMBAYARAN, NVL(t.total_bayar,0) AS TOTAL_BAYAR, t.konfirmasi_admin,
           p.nama_pelanggan, p.alamat, p.no_hp
    FROM transaksi t
    JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
    ORDER BY t.id_transaksi DESC
");
oci_execute($q);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transaksi Cucian - Smart Laundry</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        *{ margin:0; padding:0; box-sizing:border-box; }
        body{ font-family:'Poppins', sans-serif; background:#fafafa; color:#444; }
        .sidebar{ width:240px; height:100vh; position:fixed; left:0; top:0; padding:30px 20px; background:#fdf4ff; border-right:1px solid #f3e8ff; }
        .logo{ font-size:20px; font-weight:600; color:#a855f7; margin-bottom:40px; }
        .menu a{ display:block; padding:12px; border-radius:12px; text-decoration:none; color:#7c3aed; margin-bottom:10px; transition:0.3s; font-size: 15px; }
        .menu a:hover, .menu a.active{ background:#f3e8ff; }
        .active{ background:#e9d5ff !important; font-weight: 500; }
        .content{ margin-left:240px; padding:50px; width: calc(100% - 240px); }
        .header{ display:flex; justify-content:space-between; align-items:center; margin-bottom:40px; }
        .header h1{ font-weight:600; color:#7c3aed; }
        .btn-tambah{ padding:12px 24px; background:#7c3aed; color:white; text-decoration:none; border-radius:12px; font-weight:500; border:none; }
        .table-container{ background:white; padding:30px; border-radius:20px; box-shadow:0 5px 15px rgba(0,0,0,0.02); border:1px solid #f3e8ff; }
        
        .btn-status { padding: 6px 14px; border-radius: 10px; font-weight: 600; font-size: 0.75rem; border: none; min-width: 95px; }
        .st-proses { background: #fef3c7 !important; color: #92400e !important; }
        .st-selesai { background: #dcfce7 !important; color: #166534 !important; }
        
        .btn-bayar { padding: 6px 12px; border-radius: 8px; font-weight: 600; font-size: 0.75rem; border: none; min-width: 110px; }
        .bayar-lunas { background: #dcfce7 !important; color: #166534 !important; }
        .bayar-belum { background: #fee2e2 !important; color: #991b1b !important; }

        .wa-btn { background-color: #25D366; color: white; border-radius: 10px; font-weight: 500; font-size: 0.8rem; border: none; padding: 6px 12px; text-decoration: none; display: inline-block; }
        .tr-locked { background-color: #fdfafe !important; }
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
            <a href="../logout.php" style="margin-top: 30px; color: #ef4444;">Logout</a>
        </div>
    </div>

    <div class="content">
        <div class="header">
            <h1>Data Transaksi</h1>
            <a href="tambah.php" class="btn-tambah shadow-sm"><i class="bi bi-plus-lg me-1"></i> Tambah Transaksi</a>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'error_status'): ?>
            <div class="alert alert-danger border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> Validasi Gagal! Pastikan progres cucian telah <b>Selesai</b> dan status pembayaran sudah <b>Lunas</b> sebelum menyelesaikan transaksi!
            </div>
        <?php endif; ?>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th class="text-start">Pelanggan</th>
                            <th>Status Progres</th>
                            <th>Metode</th>
                            <th>Status Bayar</th>
                            <th>Total Bayar</th>
                            <th>Nota WA</th>
                            <th>Selesai</th>
                            <th>Detail</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $no = 1;
                    while($d = oci_fetch_array($q, OCI_ASSOC)){
                        $is_locked = ($d['KONFIRMASI_ADMIN'] == 1);
                        
                        // Validasi Status Progres
                        $status_db = !empty($d['STATUS']) ? strtoupper($d['STATUS']) : 'PROSES';
                        if ($status_db != "SELESAI") { $status_db = "PROSES"; }
                        
                        $status_label = ($status_db == "SELESAI") ? "Selesai" : "Proses";
                        $st_class = ($status_db == "SELESAI") ? "st-selesai" : "st-proses";
                        
                        // Validasi Status Pembayaran
                        $status_bayar = !empty($d['STATUS_PEMBAYARAN']) ? strtoupper($d['STATUS_PEMBAYARAN']) : 'BELUM LUNAS';
                        $bayar_class = ($status_bayar == "LUNAS") ? "bayar-lunas" : "bayar-belum";
                        $bayar_label = ($status_bayar == "LUNAS") ? "Lunas" : "Belum Lunas";

                        $metode = !empty($d['METODE_PEMBAYARAN']) ? htmlspecialchars($d['METODE_PEMBAYARAN']) : 'Tunai';
                        $total_tagihan = $d['TOTAL_BAYAR'];

                        // LOGIKA MATEMATIS UNTUK MENENTUKAN JENIS LAYANAN DAN BERAT SECARA OTOMATIS
                        // Cuci Kering = Rp 5.000/kg , Cuci + Setrika = Rp 7.000/kg
                        if ($total_tagihan % 7000 == 0 && $total_tagihan != 0) {
                            $layanan_pilihan = "Cuci + Setrika (Rp 7,000/kg)";
                            $berat_cucian = $total_tagihan / 7000;
                        } else if ($total_tagihan % 5000 == 0 && $total_tagihan != 0) {
                            $layanan_pilihan = "Cuci Kering (Rp 5,000/kg)";
                            $berat_cucian = $total_tagihan / 5000;
                        } else {
                            // Antisipasi jika ada nominal acak/kombinasi lain
                            $layanan_pilihan = "Reguler / Custom";
                            $berat_cucian = "-";
                        }
                        
                        // Dinamisasi Catatan Nota berdasarkan Teks Metode Pembayaran
                        if(strpos(strtolower($metode), 'transfer') !== false){
                            $note_bayar = "$metode ($nama_bank No. Rek: $norek_bank)";
                        } else {
                            $note_bayar = $metode;
                        }

                        $no_wa = formatWhatsApp($d['NO_HP']);
                        
                        // FORMAT ELEKTRONIK NOTA STRUK WHATSAPP JADI (LENGKAP 7 PARAMETER)
                        $isi_pesan = "*--- NOTA DIGITAL LAUNDRY ---*%0A" .
                                     "Halo Kak *" . htmlspecialchars($d['NAMA_PELANGGAN']) . "*, berikut adalah nota rincian cucian Anda:%0A%0A" .
                                     "*Nama Pelanggan :* " . htmlspecialchars($d['NAMA_PELANGGAN']) . "%0A" .
                                     "*Layanan Terpilih :* " . $layanan_pilihan . "%0A" .
                                     "*Berat Cucian    :* " . $berat_cucian . " Kg%0A" .
                                     "*Metode Bayar    :* " . $note_bayar . "%0A" .
                                     "*Total Bayar     :* *Rp " . number_format($total_tagihan) . "*%0A" .
                                     "*Status Bayar    :* _" . $bayar_label . "_%0A" .
                                     "*Status Progres  :* *_" . $status_label . "_*%0A%0A" .
                                     "Silakan diambil ke outlet jika sudah selesai ya, Kak. Terima kasih banyak! ";
                        
                        $wa_link = "https://api.whatsapp.com/send?phone=$no_wa&text=$isi_pesan";
                    ?>
                        <tr class="<?= $is_locked ? 'tr-locked' : ''; ?>">
                            <td><?= $no++; ?></td>
                            <td class="text-start">
                                <div class="fw-bold"><?= htmlspecialchars($d['NAMA_PELANGGAN']); ?></div>
                                <small class="text-muted" style="font-size: 11px;"><?= htmlspecialchars($d['ALAMAT']); ?></small>
                            </td>
                            
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-status <?= $st_class; ?> <?= $is_locked ? '' : 'dropdown-toggle'; ?>" type="button" <?= $is_locked ? 'disabled' : 'data-bs-toggle="dropdown"'; ?>>
                                        <?= $status_label; ?>
                                    </button>
                                    <?php if(!$is_locked): ?>
                                    <ul class="dropdown-menu border-0 shadow-sm">
                                        <li><a class="dropdown-item" href="index.php?action=update&id=<?= $d['ID_TRANSAKSI']; ?>&status=Proses">Proses</a></li>
                                        <li><a class="dropdown-item" href="index.php?action=update&id=<?= $d['ID_TRANSAKSI']; ?>&status=Selesai">Selesai</a></li>
                                    </ul>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            <td><span class="badge bg-light text-dark border fw-medium"><?= $metode; ?></span></td>
                            
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-bayar <?= $bayar_class; ?> <?= $is_locked ? '' : 'dropdown-toggle'; ?>" type="button" <?= $is_locked ? 'disabled' : 'data-bs-toggle="dropdown"'; ?>>
                                        <?= $bayar_label; ?>
                                    </button>
                                    <?php if(!$is_locked): ?>
                                    <ul class="dropdown-menu border-0 shadow-sm">
                                        <li><a class="dropdown-item" href="index.php?action=update_bayar&id=<?= $d['ID_TRANSAKSI']; ?>&status_bayar=Belum Lunas">Belum Lunas</a></li>
                                        <li><a class="dropdown-item" href="index.php?action=update_bayar&id=<?= $d['ID_TRANSAKSI']; ?>&status_bayar=Lunas">Lunas</a></li>
                                    </ul>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            <td class="fw-bold">Rp <?= number_format($total_tagihan); ?></td>
                            
                            <td>
                                <?php if($status_db == "SELESAI" && !$is_locked): ?>
                                    <a href="<?= $wa_link; ?>" target="_blank" class="wa-btn"><i class="bi bi-whatsapp"></i> Kirim</a>
                                <?php elseif($is_locked): ?>
                                    <span class="text-muted small"><i class="bi bi-check-all"></i> Terkirim</span>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if($is_locked): ?>
                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                <?php else: ?>
                                    <a href="javascript:void(0);" 
                                       class="text-secondary fs-5" 
                                       onclick="checkLock('<?= $status_db; ?>', '<?= $status_bayar; ?>', '<?= $d['ID_TRANSAKSI']; ?>')">
                                       <i class="bi bi-circle"></i>
                                    </a>
                                <?php endif; ?>
                            </td>

                            <td>
                                <a href="detail_transaksi.php?id=<?= $d['ID_TRANSAKSI']; ?>" class="btn btn-sm btn-outline-info" title="Cek Detail">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </td>

                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="edit.php?id=<?= $d['ID_TRANSAKSI']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="hapus.php?id=<?= $d['ID_TRANSAKSI']; ?>" class="btn btn-sm btn-outline-danger" title="Hapus" onclick="return confirm('Hapus data ini?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function checkLock(statusProgres, statusBayar, id) {
    if (statusProgres !== 'SELESAI' || statusBayar !== 'LUNAS') {
        alert('Maaf, transaksi tidak bisa diselesaikan!\n\nPastikan:\n1. Status Progres sudah SELESAI.\n2. Status Pembayaran sudah LUNAS.');
    } else {
        if (confirm('Konfirmasi transaksi ini?\nData konfirmasi akan disimpan ke sistem.')) {
            window.location.href = 'index.php?action=konfirmasi&id=' + id;
        }
    }
}
</script>
</body>
</html>