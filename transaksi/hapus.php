<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login.php");
    exit;
}

include "../koneksi.php";

$id = $_GET['id'];

$detail = oci_parse($conn,"
    DELETE FROM detail_transaksi
    WHERE id_transaksi = :id
");

oci_bind_by_name($detail,":id",$id);

oci_execute($detail);

$q = oci_parse($conn,"
    DELETE FROM transaksi
    WHERE id_transaksi = :id
");

oci_bind_by_name($q,":id",$id);

oci_execute($q);

header("Location: index.php");
?>