<?php
// config/db.php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'personal_web';

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Set charset agar tidak error dengan huruf aneh
mysqli_set_charset($conn, "utf8");
?>