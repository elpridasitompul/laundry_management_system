<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login.php");
    exit;
}

include "../koneksi.php";

if(isset($_POST['simpan'])){

    $keterangan = $_POST['keterangan'];
    $jumlah = $_POST['jumlah'];

    $q = "
        INSERT INTO pengeluaran
        VALUES(
            seq_pengeluaran.NEXTVAL,
            SYSDATE,
            :keterangan,
            :jumlah
        )
    ";

    $s = oci_parse($conn,$q);

    oci_bind_by_name($s,":keterangan",$keterangan);
    oci_bind_by_name($s,":jumlah",$jumlah);

    oci_execute($s);

    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html>
<head>

    <title>Tambah Pengeluaran</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="../style.css">

</head>

<body>

<div class="container mt-5">

    <div class="card shadow p-4">

        <h2 class="mb-4">Tambah Pengeluaran</h2>

        <form method="POST">

            <div class="mb-3">

                <label>Keterangan</label>

                <input type="text"
                       name="keterangan"
                       class="form-control"
                       required>

            </div>

            <div class="mb-3">

                <label>Jumlah</label>

                <input type="number"
                       name="jumlah"
                       class="form-control"
                       required>

            </div>

            <button name="simpan"
                    class="btn btn-primary">

                Simpan

            </button>

            <a href="index.php"
               class="btn btn-secondary">

               Kembali

            </a>

        </form>

    </div>

</div>

</body>
</html>