<?php
// auth.php - Funciones de autenticación y control de acceso

session_start();

// Verificar si el usuario está autenticado
function esta_autenticado() {
    return isset($_SESSION['usuario_id']) && isset($_SESSION['username']);
}

// Obtener el rol del usuario actual
function obtener_rol() {
    return $_SESSION['rol'] ?? null;
}

// Verificar si el usuario tiene un rol específico
function tiene_rol($rol_requerido) {
    if (!esta_autenticado()) return false;
    $rol_actual = obtener_rol();
    
    // Admin tiene acceso a todo
    if ($rol_actual === 'admin') return true;
    
    return $rol_actual === $rol_requerido;
}

// Verificar si el usuario tiene permiso (uno de varios roles)
function tiene_permiso($roles_permitidos = []) {
    if (!esta_autenticado()) return false;
    $rol_actual = obtener_rol();
    
    // Admin tiene acceso a todo
    if ($rol_actual === 'admin') return true;
    
    return in_array($rol_actual, $roles_permitidos);
}

// Requerir autenticación - redirige al login si no está autenticado
function requerir_autenticacion() {
    if (!esta_autenticado()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: login.php');
        exit;
    }
}

// Requerir un rol específico
function requerir_rol($rol_requerido) {
    requerir_autenticacion();
    if (!tiene_rol($rol_requerido)) {
        header('Location: sin_permiso.php');
        exit;
    }
}

// Requerir uno de varios roles
function requerir_permiso($roles_permitidos = []) {
    requerir_autenticacion();
    if (!tiene_permiso($roles_permitidos)) {
        header('Location: sin_permiso.php');
        exit;
    }
}

// Login de usuario
function login_usuario($mysqli, $username, $password) {
    $stmt = $mysqli->prepare("SELECT u.id, u.username, u.password_hash, u.nombre_completo, u.activo, r.nombre as rol 
                              FROM usuarios u 
                              LEFT JOIN roles r ON u.rol_id = r.id 
                              WHERE u.username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    
    if (!$usuario) {
        return ['success' => false, 'mensaje' => 'Usuario no encontrado'];
    }
    
    if (!$usuario['activo']) {
        return ['success' => false, 'mensaje' => 'Usuario inactivo'];
    }
    
    if (!password_verify($password, $usuario['password_hash'])) {
        return ['success' => false, 'mensaje' => 'Contraseña incorrecta'];
    }
    
    // Login exitoso - crear sesión
    session_regenerate_id(true);
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['username'] = $usuario['username'];
    $_SESSION['nombre_completo'] = $usuario['nombre_completo'];
    $_SESSION['rol'] = $usuario['rol'];
    
    // Actualizar último acceso
    $update = $mysqli->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
    $update->bind_param("i", $usuario['id']);
    $update->execute();
    
    // Registrar sesión (opcional)
    $session_id = session_id();
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $mysqli->query("INSERT INTO sesiones (id, usuario_id, ip_address, user_agent) 
                    VALUES ('$session_id', {$usuario['id']}, '$ip', '$user_agent')
                    ON DUPLICATE KEY UPDATE last_activity = NOW()");
    
    return ['success' => true, 'mensaje' => 'Login exitoso'];
}

// Logout de usuario
function logout_usuario($mysqli) {
    if (isset($_SESSION['usuario_id'])) {
        $session_id = session_id();
        $mysqli->query("DELETE FROM sesiones WHERE id = '$session_id'");
    }
    
    session_unset();
    session_destroy();
    
    // Iniciar nueva sesión limpia
    session_start();
    session_regenerate_id(true);
}

// Obtener información del usuario actual
function obtener_usuario_actual() {
    if (!esta_autenticado()) return null;
    
    return [
        'id' => $_SESSION['usuario_id'],
        'username' => $_SESSION['username'],
        'nombre_completo' => $_SESSION['nombre_completo'] ?? '',
        'rol' => $_SESSION['rol'] ?? ''
    ];
}

// Verificar si puede editar (admin o vendedor)
function puede_editar() {
    return tiene_permiso(['admin', 'vendedor']);
}

// Verificar si puede eliminar (solo admin)
function puede_eliminar() {
    return tiene_rol('admin');
}
?>