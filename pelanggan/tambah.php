<?php
include "../koneksi.php";

if(isset($_POST['simpan'])){

    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $hp = $_POST['hp'];

    $q = oci_parse($conn,"
        INSERT INTO pelanggan
        VALUES(
            seq_pelanggan.NEXTVAL,
            :nama,
            :alamat,
            :hp
        )
    ");

    oci_bind_by_name($q,":nama",$nama);
    oci_bind_by_name($q,":alamat",$alamat);
    oci_bind_by_name($q,":hp",$hp);

    oci_execute($q);

    header("Location:index.php");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Tambah Pelanggan</title>
<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="../style.css">
</head>
<body>

<div class="content">

<div class="card p-4">

<h3 class="mb-4">Tambah Pelanggan</h3>

<form method="POST">

<div class="mb-3">
<label>Nama</label>
<input type="text" name="nama" class="form-control" required>
</div>

<div class="mb-3">
<label>Alamat</label>
<textarea name="alamat" class="form-control"></textarea>
</div>

<div class="mb-3">
<label>No HP</label>
<input type="text" name="hp" class="form-control">
</div>

<button class="btn btn-primary" name="simpan">
Simpan
</button>

</form>

</div>

</div>

</body>
</html>