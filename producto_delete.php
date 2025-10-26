<?php
require 'db_connect.php';
require 'auth.php';

// Solo admin puede eliminar productos
requerir_rol('admin');

$id = intval($_GET['id'] ?? 0);

if ($id) {
    try {
        $stmt = $mysqli->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['mensaje_exito'] = 'Producto eliminado exitosamente';
        } else {
            $_SESSION['mensaje_error'] = 'Error al eliminar el producto';
        }
    } catch (Exception $e) {
        $_SESSION['mensaje_error'] = 'Error al eliminar: ' . $e->getMessage();
    }
} else {
    $_SESSION['mensaje_error'] = 'ID de producto inválido';
}

header('Location: productos_list.php');
exit;
?>