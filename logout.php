<?php
require 'db_connect.php';
require 'auth.php';

logout_usuario($mysqli);

header('Location: login.php');
exit;
?>