<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login.php");
    exit;
}

include "../koneksi.php";

$id = $_GET['id'];

$q = oci_parse($conn,"
    SELECT * FROM layanan
    WHERE id_layanan = :id
");

oci_bind_by_name($q,":id",$id);

oci_execute($q);

$d = oci_fetch_array($q,OCI_ASSOC);

if(isset($_POST['update'])){

    $nama = $_POST['nama'];
    $harga = $_POST['harga'];

    $u = "
        UPDATE layanan
        SET
            nama_layanan = :nama,
            harga_per_kg = :harga
        WHERE id_layanan = :id
    ";

    $s = oci_parse($conn,$u);

    oci_bind_by_name($s,":nama",$nama);
    oci_bind_by_name($s,":harga",$harga);
    oci_bind_by_name($s,":id",$id);

    oci_execute($s);

    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html>
<head>

    <title>Edit Layanan</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="../style.css">

</head>

<body>

<div class="container mt-5">

    <div class="card shadow p-4">

        <h2 class="mb-4">Edit Layanan</h2>

        <form method="POST">

            <div class="mb-3">

                <label>Nama Layanan</label>

                <input type="text"
                       name="nama"
                       class="form-control"
                       value="<?= $d['NAMA_LAYANAN']; ?>"
                       required>

            </div>

            <div class="mb-3">

                <label>Harga Per Kg</label>

                <input type="number"
                       name="harga"
                       class="form-control"
                       value="<?= $d['HARGA_PER_KG']; ?>"
                       required>

            </div>

            <button name="update"
                    class="btn btn-primary">

                Update

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