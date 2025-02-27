<?php
$server = "localhost";
$user = "root";
$pass = "";
$database = "tabungan";

// Menyambungkan ke Server
$conn = new mysqli($server, $user, $pass);

// Mencoba menyambungkan ke database jika tidak ada membuat database baru
try {
    $conn->select_db($database);
} catch (Exception $e) {
    $sql = "CREATE DATABASE $database";
    $conn->query($sql);
    $conn->select_db($database);

    $conn->query(
        "
        CREATE TABLE `$database`.`users` (
        id int(11) NOT NULL AUTO_INCREMENT,
        username varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        password varchar(64) NOT NULL,
        PRIMARY KEY(id));
        ");
    
}
// sudah terkoneksi ke server dan database



// Close the connection when done
// $conn->close();

?>