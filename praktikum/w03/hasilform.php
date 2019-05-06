<?php

session_start();

$uname = $_POST['uname'] ?? null;
$password = $_POST['pwd'] ?? null;

if ($uname == 'informatika' && $password == 'uns') {
    $_SESSION['user'] = $uname;
    header('Location: berhasil.php');
} else {
    echo 'Gagal Login!';
}