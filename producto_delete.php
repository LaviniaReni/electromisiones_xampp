<?php
require 'db_connect.php';
$id = intval($_GET['id'] ?? 0);
if ($id) {
    $stmt = $mysqli->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}
header('Location: productos_list.php');
exit;
?>