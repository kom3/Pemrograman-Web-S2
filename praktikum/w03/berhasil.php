<?php

session_start();

if (isset($_SESSION['user'])) {
    echo "Selamat datang {$_SESSION['user']}";
    echo '<br><a href="logout.php">Logout</a>';
} else {
    echo "Kamu harus login dulu!";
}
?>