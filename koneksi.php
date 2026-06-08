<?php

$conn = oci_connect(
    "system",
    "sys123",
    "//localhost:1521/FREEPDB1"
);

if(!$conn){

    $e = oci_error();

    die("Koneksi gagal : " . $e['message']);
}

?>