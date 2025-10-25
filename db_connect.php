<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db   = 'electromisiones_db';
$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) die("Error conexión MySQL: " . $mysqli->connect_error);
$mysqli->set_charset("utf8mb4");
?>