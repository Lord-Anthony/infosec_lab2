<?php
$conn = mysqli_connect('localhost', 'root', '', 'infosec_lab');

if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
