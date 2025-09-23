<?php
// Gegevens voor de connectie
$host       = '127.0.0.1';
$username   = 'root';
$password   = '';
$database   = 'prototype_nutribot';
//$port       = 3307;


// 	utf8mb4_0900_ai_ci GEBRUIK DEZE FORMAT OM DE DATABASE TE MAKEN

$db = mysqli_connect($host, $username, $password, $database, $port)
or die('Error: '.mysqli_connect_error());
?>
