<?php
$servername = "localhost";
$dbuname = "root";
$dbpass = "";
$dbname = "project2";


$conn = mysqli_connect($servername, $dbuname, $dbpass, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// PDO connection for inserts
$pdo = new PDO("mysql:host=$servername;dbname=$dbname", $dbuname, $dbpass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
