<?php
require 'db_connect.php';
require 'auth.php';

// Solo admin puede editar usuarios
requerir_rol('admin');

$usuario_actual = obtener_usuario_actual();
$id = intval($_GET['id'] ?? 0);

if (!$id) {
    header('Location: usuarios_list.php');
    exit;
}

// Obtener usuario a editar
$stmt = $mysqli->prepare("SELECT u.*, r.nombre as rol_nombre FROM usuarios u LEFT JOIN roles r ON u.rol_id = r.id WHERE u.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

if (!$usuario) {
    $_SESSION['mensaje_error'] = 'Usuario no encontrado';
    header('Location: usuarios_list.php');
    exit;
}

$error = '';
$es_usuario_actual = ($id == $usuario_actual['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $nombre_completo = trim($_POST['nombre_completo'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $rol_id = intval($_POST['rol_id'] ?? 0);
    $activo = isset($_POST['activo']) ? 1 : 0;
    $cambiar_password = !empty($_POST['password']);
    
    // Validaciones
    if (empty($username) || empty($nombre_completo)) {
        $error = 'Usuario y nombre completo son obligatorios';
    } elseif (strlen($username) < 3) {
        $error = 'El usuario debe tener al menos 3 caracteres';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El email no es válido';
    } elseif (!in_array($rol_id, [1, 2, 3])) {
        $error = 'Debe seleccionar un rol válido';
    } elseif ($cambiar_password) {
        $password = $_POST['password'];
        $password_confirm = $_POST['password_confirm'] ?? '';
        
        if (strlen($password) < 6) {
            $error = 'La contraseña debe tener al menos 6 caracteres';
        } elseif ($password !== $password_confirm) {
            $error = 'Las contraseñas no coinciden';
        }
    }
    
    // Validación especial: no puede desactivarse a sí mismo
    if ($es_usuario_actual && !$activo) {
        $error = 'No puedes desactivar tu propia cuenta';
    }
    
    // Validación: no puede quitarse el rol admin a sí mismo
    if ($es_usuario_actual && $rol_id != 1) {
        $error = 'No puedes cambiar tu propio rol de administrador';
    }
    
    if (empty($error)) {
        try {
            // Verificar que el username no exista (excepto el actual)
            $check = $mysqli->prepare("SELECT id FROM usuarios WHERE username = ? AND id != ?");
            $check->bind_param("si", $username, $id);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $error = 'El nombre de usuario ya existe';
            } else {
                if ($cambiar_password) {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $mysqli->prepare("UPDATE usuarios SET username=?, nombre_completo=?, email=?, rol_id=?, activo=?, password_hash=? WHERE id=?");
                    $stmt->bind_param("sssiisi", $username, $nombre_completo, $email, $rol_id, $activo, $password_hash, $id);
                } else {
                    $stmt = $mysqli->prepare("UPDATE usuarios SET username=?, nombre_completo=?, email=?, rol_id=?, activo=? WHERE id=?");
                    $stmt->bind_param("sssiii", $username, $nombre_completo, $email, $rol_id, $activo, $id);
                }
                
                if ($stmt->execute()) {
                    $mensaje = "Usuario '{$username}' actualizado exitosamente";
                    if ($cambiar_password) {
                        $mensaje .= ' (contraseña cambiada)';
                    }
                    $_SESSION['mensaje_exito'] = $mensaje;
                    header('Location: usuarios_list.php');
                    exit;
                } else {
                    $error = 'Error al actualizar el usuario';
                }
            }
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

$roles = $mysqli->query("SELECT id, nombre, descripcion FROM roles ORDER BY id");
?>
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='utf-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Usuario - Electro Misiones</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .header h1 {
            color: #333;
            font-size: 24px;
        }
        .content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
        }
        .user-id {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #666;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }
        .required {
            color: #ff6b6b;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        input:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .hint {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .checkbox-group input[type="checkbox"] {
            width: auto;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            font-size: 14px;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        .role-option {
            margin-bottom: 10px;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .role-option:hover {
            border-color: #667eea;
            background: #f8f9fa;
        }
        .role-option input[type="radio"] {
            margin-right: 10px;
        }
        .role-option strong {
            color: #333;
        }
        .password-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .password-section h3 {
            margin-bottom: 15px;
            color: #333;
            font-size: 16px;
        }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>✏️ Editar Usuario</h1>
    </div>

    <div class="content">
        <div class="user-id">
            👤 Editando usuario ID: <strong><?= htmlspecialchars($usuario['id']) ?></strong>
            <?php if ($es_usuario_actual): ?>
                <span style="color: #667eea;"> (Tu cuenta)</span>
            <?php endif; ?>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($es_usuario_actual): ?>
            <div class="warning-box">
                ⚠️ <strong>Estás editando tu propia cuenta.</strong> No puedes cambiar tu rol de administrador ni desactivar tu cuenta.
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="form-row">
                <div class="form-group">
                    <label>Usuario <span class="required">*</span></label>
                    <input type="text" name="username" required 
                           value="<?= htmlspecialchars($usuario['username']) ?>"
                           minlength="3" autofocus>
                    <div class="hint">Mínimo 3 caracteres, sin espacios</div>
                </div>

                <div class="form-group">
                    <label>Nombre Completo <span class="required">*</span></label>
                    <input type="text" name="nombre_completo" required
                           value="<?= htmlspecialchars($usuario['nombre_completo']) ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email"
                       value="<?= htmlspecialchars($usuario['email']) ?>">
                <div class="hint">Opcional</div>
            </div>

            <div class="form-group">
                <label>Rol del Usuario <span class="required">*</span></label>
                <?php if ($es_usuario_actual): ?>
                    <div class="hint" style="margin-bottom: 10px; color: #856404;">
                        🔒 No puedes cambiar tu propio rol de administrador
                    </div>
                <?php endif; ?>
                
                <?php 
                $roles->data_seek(0);
                while($r = $roles->fetch_assoc()): 
                ?>
                    <label class="role-option">
                        <input type="radio" name="rol_id" value="<?= $r['id'] ?>" 
                               <?= ($usuario['rol_id'] == $r['id']) ? 'checked' : '' ?>
                               <?= ($es_usuario_actual && $r['id'] != 1) ? 'disabled' : '' ?>
                               required>
                        <strong><?= htmlspecialchars(ucfirst($r['nombre'])) ?></strong>
                        - <?= htmlspecialchars($r['descripcion']) ?>
                    </label>
                <?php endwhile; ?>
            </div>

            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" name="activo" id="activo" 
                           <?= $usuario['activo'] ? 'checked' : '' ?>
                           <?= $es_usuario_actual ? 'disabled' : '' ?>>
                    <label for="activo" style="margin: 0;">Usuario Activo</label>
                </div>
                <div class="hint">
                    <?php if ($es_usuario_actual): ?>
                        🔒 No puedes desactivar tu propia cuenta
                    <?php else: ?>
                        Los usuarios inactivos no pueden iniciar sesión
                    <?php endif; ?>
                </div>
            </div>

            <div class="password-section">
                <h3>🔐 Cambiar Contraseña</h3>
                <div class="hint" style="margin-bottom: 15px;">
                    Deja estos campos vacíos si NO quieres cambiar la contraseña
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Nueva Contraseña</label>
                        <input type="password" name="password" minlength="6">
                        <div class="hint">Mínimo 6 caracteres</div>
                    </div>

                    <div class="form-group">
                        <label>Confirmar Nueva Contraseña</label>
                        <input type="password" name="password_confirm" minlength="6">
                    </div>
                </div>
            </div>

            <div style="background: #e7f3ff; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-size: 13px;">
                <strong>📊 Información adicional:</strong><br>
                <div style="margin-top: 10px;">
                    • Creado: <?= date('d/m/Y H:i', strtotime($usuario['created_at'])) ?><br>
                    • Último acceso: <?= $usuario['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])) : 'Nunca' ?>
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">💾 Actualizar Usuario</button>
                <a href="usuarios_list.php" class="btn btn-secondary">❌ Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>