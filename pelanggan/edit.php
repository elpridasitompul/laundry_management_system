<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login.php");
    exit;
}

include "../koneksi.php";

$id = $_GET['id'];

$q = oci_parse($conn,"
    SELECT * FROM pelanggan
    WHERE id_pelanggan = '$id'
");

oci_execute($q);

$d = oci_fetch_array($q,OCI_ASSOC);

if(isset($_POST['update'])){

    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $hp = $_POST['hp'];

    $u = "
        UPDATE pelanggan
        SET
            nama_pelanggan = :nama,
            alamat = :alamat,
            no_hp = :hp
        WHERE id_pelanggan = :id
    ";

    $s = oci_parse($conn,$u);

    oci_bind_by_name($s,":nama",$nama);
    oci_bind_by_name($s,":alamat",$alamat);
    oci_bind_by_name($s,":hp",$hp);
    oci_bind_by_name($s,":id",$id);

    oci_execute($s);

    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html>
<head>

    <title>Edit Pelanggan</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="../style.css">

</head>

<body>

<div class="container mt-5">

    <div class="card shadow p-4">

        <h2 class="mb-4">Edit Pelanggan</h2>

        <form method="POST">

            <div class="mb-3">

                <label>Nama Pelanggan</label>

                <input type="text"
                       name="nama"
                       class="form-control"
                       value="<?= $d['NAMA_PELANGGAN']; ?>"
                       required>

            </div>

            <div class="mb-3">

                <label>Alamat</label>

                <textarea name="alamat"
                          class="form-control"
                          required><?= $d['ALAMAT']; ?></textarea>

            </div>

            <div class="mb-3">

                <label>No HP</label>

                <input type="text"
                       name="hp"
                       class="form-control"
                       value="<?= $d['NO_HP']; ?>"
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