<?php
session_start();
include "koneksi.php";

$error = "";

if(isset($_POST['login'])){

    $username = $_POST['username'];
    $password = $_POST['password'];

    $q = oci_parse($conn,"
        SELECT *
        FROM admin
        WHERE username = :username
    ");

    oci_bind_by_name($q, ":username", $username);

    oci_execute($q);

    $data = oci_fetch_array($q, OCI_ASSOC);

    if($data){

        if($password == $data['PASSWORD']){

            $_SESSION['login'] = true;
            $_SESSION['id_admin'] = $data['ID_ADMIN'];
            $_SESSION['nama_admin'] = $data['NAMA_ADMIN'];

            header("Location: dashboard.php");
            exit;

        }else{

            $error = "Password salah";
        }

    }else{

        $error = "Username tidak ditemukan";
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Login Smart Laundry</title>

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

<style>

body{
    background:#f1f5f9;
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    font-family:Arial;
}

.login-box{
    width:400px;
    background:white;
    padding:40px;
    border-radius:20px;
    box-shadow:0 5px 20px rgba(0,0,0,0.08);
}

</style>

</head>
<body>

<div class="login-box">

<h2 class="text-center mb-4">
Laundry
</h2>

<?php if($error != ""){ ?>

<div class="alert alert-danger">

<?= $error; ?>

</div>

<?php } ?>

<form method="POST">

<div class="mb-3">

<label>Username</label>

<input type="text"
name="username"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Password</label>

<input type="password"
name="password"
class="form-control"
required>

</div>

<button type="submit"
name="login"
class="btn btn-primary w-100">

Login

</button>

</form>

</div>

</body>
</html>