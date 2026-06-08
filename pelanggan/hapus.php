<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login.php");
    exit;
}

include "../koneksi.php";

$id = $_GET['id'];

$q = oci_parse($conn,"
    DELETE FROM pelanggan
    WHERE id_pelanggan = :id
");

oci_bind_by_name($q,":id",$id);

oci_execute($q);

header("Location: index.php");
?>