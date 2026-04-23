
<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "netcare";

$mysqli = new mysqli($host, $user, $pass, $dbname);

if ($mysqli->connect_error) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8mb4");
?>