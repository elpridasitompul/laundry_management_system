<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login.php");
    exit;
}

include "../koneksi.php";

if(isset($_POST['simpan'])){

    $nama = $_POST['nama'];
    $harga = $_POST['harga'];

    $q = "
        INSERT INTO layanan
        VALUES(
            seq_layanan.NEXTVAL,
            :nama,
            :harga
        )
    ";

    $s = oci_parse($conn,$q);

    oci_bind_by_name($s,":nama",$nama);
    oci_bind_by_name($s,":harga",$harga);

    oci_execute($s);

    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html>
<head>

    <title>Tambah Layanan</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="../style.css">

</head>

<body>

<div class="container mt-5">

    <div class="card shadow p-4">

        <h2 class="mb-4">Tambah Layanan</h2>

        <form method="POST">

            <div class="mb-3">

                <label>Nama Layanan</label>

                <input type="text"
                       name="nama"
                       class="form-control"
                       required>

            </div>

            <div class="mb-3">

                <label>Harga Per Kg</label>

                <input type="number"
                       name="harga"
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