<?php
session_start();
include "../koneksi.php";

if(isset($_POST['simpan'])){
    $id_p = $_POST['id_pelanggan'];
    $layanan_pilihan = isset($_POST['layanan']) ? $_POST['layanan'] : [];
    $metode = $_POST['metode'];
    $kode = "TRX-" . date('dmyHis');

    // MENGATASI ERROR ORA-01400 (ID_ADMIN WAKIB DIISI)
    // Mengambil ID Admin dari session login, jika belum ada otomatis diset ke 1
    $id_admin = isset($_SESSION['id_admin']) ? $_SESSION['id_admin'] : 1; 

    // ==========================================
    // 1. INSERT TRANSAKSI UTAMA (TERMASUK ID_ADMIN)
    // ==========================================
    $sql_t = "
        INSERT INTO transaksi (
            ID_TRANSAKSI, ID_PELANGGAN, ID_ADMIN, KODE_TRANSAKSI, TANGGAL_MASUK, 
            STATUS, METODE_PEMBAYARAN, STATUS_PEMBAYARAN, TOTAL_BAYAR, KONFIRMASI_ADMIN
        )
        VALUES (
            seq_transaksi.NEXTVAL, :idp, :idadmin, :kode, SYSDATE, 
            'Proses', :metode, 'Belum Lunas', 0, 0
        )
        RETURNING ID_TRANSAKSI INTO :last_id
    ";

    $stmt_t = oci_parse($conn, $sql_t);
    oci_bind_by_name($stmt_t, ":idp", $id_p);
    oci_bind_by_name($stmt_t, ":idadmin", $id_admin); // Binding ID Admin agar tidak NULL lagi
    oci_bind_by_name($stmt_t, ":kode", $kode);
    oci_bind_by_name($stmt_t, ":metode", $metode);
    
    $last_id = 0;
    oci_bind_by_name($stmt_t, ":last_id", $last_id, 32);
    
    $execute_t = oci_execute($stmt_t, OCI_NO_AUTO_COMMIT);

    if($execute_t && $last_id > 0) {
        $total_bayar = 0;
        $error_detail = false;

        // ==========================================
        // 2. INSERT DETAIL TRANSAKSI (LOOPING)
        // ==========================================
        foreach($layanan_pilihan as $id_l){
            $berat = isset($_POST['berat'][$id_l]) ? floatval($_POST['berat'][$id_l]) : 0;

            // AMBIL HARGA LAYANAN
            $qHarga = oci_parse($conn, "SELECT harga_per_kg FROM layanan WHERE id_layanan = :id");
            oci_bind_by_name($qHarga, ":id", $id_l);
            oci_execute($qHarga);
            $h = oci_fetch_array($qHarga, OCI_ASSOC);
            
            $harga = isset($h['HARGA_PER_KG']) ? $h['HARGA_PER_KG'] : 0;
            $subtotal = $harga * $berat;
            $total_bayar += $subtotal;

            $sql_d = "
                INSERT INTO detail_transaksi (ID_DETAIL, ID_TRANSAKSI, ID_LAYANAN, BERAT, SUBTOTAL)
                VALUES (seq_detail.NEXTVAL, :idt, :idl, :berat, :subtotal)
            ";

            $stmt_d = oci_parse($conn, $sql_d);
            
            $current_trx_id = $last_id; 
            oci_bind_by_name($stmt_d, ":idt", $current_trx_id);
            oci_bind_by_name($stmt_d, ":idl", $id_l);
            oci_bind_by_name($stmt_d, ":berat", $berat);
            oci_bind_by_name($stmt_d, ":subtotal", $subtotal);

            if(!oci_execute($stmt_d, OCI_NO_AUTO_COMMIT)) {
                $error_detail = true;
                break;
            }
        }

        // ==========================================
        // 3. UPDATE TOTAL BAYAR & COMMIT DATA
        // ==========================================
        if(!$error_detail) {
            $upTotal = oci_parse($conn, "UPDATE transaksi SET total_bayar = :total WHERE id_transaksi = :id");
            oci_bind_by_name($upTotal, ":total", $total_bayar);
            oci_bind_by_name($upTotal, ":id", $current_trx_id);
            
            if(oci_execute($upTotal, OCI_NO_AUTO_COMMIT)) {
                oci_commit($conn); // Sukses menyeluruh, simpan permanen
                echo "<script>alert('Transaksi berhasil ditambahkan!'); window.location='index.php';</script>";
                exit;
            }
        }
        
        oci_rollback($conn); // Batalkan jika detail bermasalah
        echo "<script>alert('Gagal memperbarui total harga transaksi.');</script>";
    } else {
        $e = oci_error($stmt_t);
        echo "<script>alert('Gagal menyimpan transaksi utama: " . htmlentities($e['message']) . "');</script>";
    }
}

// AMBIL DATA DROP DOWN
$qp = oci_parse($conn, "SELECT * FROM pelanggan"); oci_execute($qp);
$ql = oci_parse($conn, "SELECT * FROM layanan"); oci_execute($ql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Transaksi - Smart Laundry</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{ background:#fafafa; font-family:'Poppins', sans-serif; }
        .sidebar{ width:240px; height:100vh; position:fixed; background:#fdf4ff; padding:30px 20px; border-right:1px solid #f3e8ff; }
        .menu a{ display:block; padding:12px; color:#7c3aed; text-decoration:none; margin-bottom:10px; border-radius:12px; }
        .content{ margin-left:240px; padding:50px; }
        .card-form{ background:white; padding:30px; border-radius:20px; border:1px solid #f3e8ff; box-shadow:0 5px 15px rgba(0,0,0,0.01); }
        .btn-ungu{ background:#7c3aed; color:white; border-radius:10px; padding:10px; border:none; font-weight:500; }
        .btn-ungu:hover{ background:#6d28d9; }
        .layanan-box{ border:1px solid #f3e8ff; border-radius:12px; padding:15px; margin-bottom:15px; background:#fafafa; }
        .total-box{ background:#f3e8ff; padding:20px; border-radius:15px; margin-top:20px; color:#6d28d9; }
        .pembayaran-info-box { background: #fffbeb; border: 1px solid #fde68a; padding: 15px; border-radius: 12px; margin-top: 15px; display: none; }
    </style>
</head>
<body>

<div class="sidebar">
    <h4 style="color:#7c3aed; margin-bottom:30px; font-weight:600;">Laundry</h4>
    <div class="menu">
        <a href="../dashboard.php">Dashboard</a>
        <a href="index.php" style="background:#e9d5ff; font-weight:600;">Transaksi Cucian</a>
    </div>
</div>

<div class="content">
    <div class="card-form">
        <h2 style="color:#7c3aed; font-weight:600;" class="mb-4">Tambah Transaksi Baru</h2>
        <form method="POST">
            
            <label class="fw-bold mb-1">Pelanggan</label>
            <select name="id_pelanggan" class="form-select mb-4" required>
                <option value="">-- Pilih Pelanggan --</option>
                <?php while($p = oci_fetch_array($qp, OCI_ASSOC)): ?>
                    <option value="<?= $p['ID_PELANGGAN']; ?>"><?= $p['NAMA_PELANGGAN']; ?></option>
                <?php endwhile; ?>
            </select>

            <label class="fw-bold mb-3">Pilih Layanan</label>
            <?php while($l = oci_fetch_array($ql, OCI_ASSOC)): ?>
                <div class="layanan-box">
                    <div class="form-check mb-2">
                        <input class="form-check-input layanan-check" type="checkbox" name="layanan[]" value="<?= $l['ID_LAYANAN']; ?>" data-harga="<?= $l['HARGA_PER_KG']; ?>">
                        <label class="form-check-label fw-semibold">
                            <?= $l['NAMA_LAYANAN']; ?> - Rp <?= number_format($l['HARGA_PER_KG']); ?>/kg
                        </label>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <label class="small text-muted mb-1">Berat Cucian (kg)</label>
                            <input type="number" step="0.1" min="0.1" name="berat[<?= $l['ID_LAYANAN']; ?>]" class="form-control berat-input" placeholder="Contoh: 2">
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>

            <div class="row mt-4">
                <div class="col-md-12 mb-3">
                    <label class="fw-bold mb-1">Metode Pembayaran</label>
                    <select name="metode" id="metodeBayar" class="form-select" required>
                        <option value="Tunai">Tunai (Cash)</option>
                        <option value="Transfer">Transfer Bank</option>
                        <option value="E-Wallet">E-Wallet (DANA)</option>
                    </select>

                    <div id="infoPembayaran" class="pembayaran-info-box">
                        <p class="mb-1 fw-bold" id="titleInfo" style="color: #b45309;"></p>
                        <ul class="mb-0" id="detailInfo" style="color: #78350f;">
                            </ul>
                    </div>
                </div>
            </div>

            <div class="total-box">
                <h5 class="mb-0 fw-bold">Total Bayar : <span id="totalBayar">Rp 0</span></h5>
            </div>

            <button type="submit" name="simpan" class="btn-ungu w-100 mt-4 shadow-sm">Simpan Transaksi</button>
        </form>
    </div>
</div>

<script>
// Fungsi Kalkulator Hitung Total Otomatis
function hitungTotal(){
    let total = 0;
    document.querySelectorAll('.layanan-check').forEach((check) => {
        if(check.checked){
            let harga = parseInt(check.dataset.harga);
            let parent = check.closest('.layanan-box');
            let beratInput = parent.querySelector('.berat-input');
            let berat = parseFloat(beratInput.value) || 0;
            total += harga * berat;
        }
    });
    document.getElementById('totalBayar').innerHTML = 'Rp ' + total.toLocaleString('id-ID');
}

// Fungsi Dynamic Box untuk Transfer & E-Wallet (DANA)
function cekMetodePembayaran() {
    let metode = document.getElementById('metodeBayar').value;
    let infoBox = document.getElementById('infoPembayaran');
    let titleInfo = document.getElementById('titleInfo');
    let detailInfo = document.getElementById('detailInfo');

    if (metode === 'Transfer') {
        infoBox.style.display = 'block';
        titleInfo.innerHTML = 'Informasi Pembayaran Transfer Bank:';
        detailInfo.innerHTML = '<li><strong>Bank:</strong> Bank Mandiri</li>' +
                               '<li><strong>No. Rekening:</strong> 123-456-7890</li>' +
                               '<li><strong>Atas Nama:</strong> Elprida Sitompul</li>';
    } else if (metode === 'E-Wallet') {
        infoBox.style.display = 'block';
        titleInfo.innerHTML = 'Informasi Pembayaran E-Wallet:';
        detailInfo.innerHTML = '<li><strong>Provider:</strong> DANA</li>' +
                               '<li><strong>No. HP / DANA:</strong> 0812-3456-7890</li>' +
                               '<li><strong>Atas Nama:</strong> Elprida Sitompul</li>';
    } else {
        infoBox.style.display = 'none'; // Sembunyikan kalau Tunai
    }
}

// Event Listeners
document.querySelectorAll('.layanan-check, .berat-input').forEach((el) => {
    el.addEventListener('input', hitungTotal);
});
document.getElementById('metodeBayar').addEventListener('change', cekMetodePembayaran);
</script>
</body>
</html>