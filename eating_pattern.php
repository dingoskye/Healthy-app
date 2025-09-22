<?php
/** @var mysqli $db */
require_once 'includes/database.php';
session_start();

$day = [
    ["day" => "Monday", "date" => "22 sep", "morning"],
    ["day" => "Tueday", "date" => "23 sep", "morning"],
    ["day" => "Wednesday", "date" => "24 sep", "morning"],
];

$currentday = strtolower(date("l"));

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Eating Pattern</title>
</head>
<body>

<nav>
    <a href="#">settings</a>
    <a href="#">notifications</a>
</nav>

<main>
</main>

<footer>
</footer>

</body>
</html>
