<?php
require 'db_connect.php';
require 'auth.php';

// Solo admin puede eliminar usuarios
requerir_rol('admin');

$usuario_actual = obtener_usuario_actual();
$id = intval($_GET['id'] ?? 0);

// Validaciones de seguridad
if ($id <= 0) {
    $_SESSION['mensaje_error'] = 'ID de usuario inválido';
    header('Location: usuarios_list.php');
    exit;
}

// No puede eliminarse a sí mismo
if ($id == $usuario_actual['id']) {
    $_SESSION['mensaje_error'] = 'No puedes eliminar tu propia cuenta';
    header('Location: usuarios_list.php');
    exit;
}

// Verificar que el usuario existe
$stmt = $mysqli->prepare("SELECT username FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if (!$usuario) {
    $_SESSION['mensaje_error'] = 'Usuario no encontrado';
    header('Location: usuarios_list.php');
    exit;
}

try {
    // Eliminar sesiones del usuario primero (por foreign key)
    $delete_sesiones = $mysqli->prepare("DELETE FROM sesiones WHERE usuario_id = ?");
    $delete_sesiones->bind_param("i", $id);
    $delete_sesiones->execute();
    
    // Eliminar usuario
    $delete_usuario = $mysqli->prepare("DELETE FROM usuarios WHERE id = ?");
    $delete_usuario->bind_param("i", $id);
    
    if ($delete_usuario->execute()) {
        $_SESSION['mensaje_exito'] = "Usuario '{$usuario['username']}' eliminado exitosamente";
    } else {
        $_SESSION['mensaje_error'] = 'Error al eliminar el usuario';
    }
} catch (Exception $e) {
    $_SESSION['mensaje_error'] = 'Error al eliminar: ' . $e->getMessage();
}

header('Location: usuarios_list.php');
exit;
?>