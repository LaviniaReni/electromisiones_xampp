<?php
require 'db_connect.php';
require 'auth.php';

// Solo admin puede agregar usuarios
requerir_rol('admin');

$usuario_actual = obtener_usuario_actual();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $nombre_completo = trim($_POST['nombre_completo'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $rol_id = intval($_POST['rol_id'] ?? 0);
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Validaciones
    if (empty($username) || empty($password) || empty($nombre_completo)) {
        $error = 'Usuario, contraseña y nombre completo son obligatorios';
    } elseif (strlen($username) < 3) {
        $error = 'El usuario debe tener al menos 3 caracteres';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    } elseif ($password !== $password_confirm) {
        $error = 'Las contraseñas no coinciden';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El email no es válido';
    } elseif (!in_array($rol_id, [1, 2, 3])) {
        $error = 'Debe seleccionar un rol válido';
    } else {
        try {
            // Verificar que el username no exista
            $check = $mysqli->prepare("SELECT id FROM usuarios WHERE username = ?");
            $check->bind_param("s", $username);
            $check->execute();
            
            if ($check->get_result()->num_rows > 0) {
                $error = 'El nombre de usuario ya existe';
            } else {
                // Verificar email si se proporcionó
                if (!empty($email)) {
                    $check_email = $mysqli->prepare("SELECT id FROM usuarios WHERE email = ?");
                    $check_email->bind_param("s", $email);
                    $check_email->execute();
                    
                    if ($check_email->get_result()->num_rows > 0) {
                        $error = 'El email ya está registrado';
                    }
                }
                
                if (empty($error)) {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    
                    $stmt = $mysqli->prepare("INSERT INTO usuarios (username, password_hash, nombre_completo, email, rol_id, activo) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssii", $username, $password_hash, $nombre_completo, $email, $rol_id, $activo);
                    
                    if ($stmt->execute()) {
                        $_SESSION['mensaje_exito'] = "Usuario '{$username}' creado exitosamente";
                        header('Location: usuarios_list.php');
                        exit;
                    } else {
                        $error = 'Error al crear el usuario';
                    }
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
    <title>Agregar Usuario - Electro Misiones</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            color: #333;
            font-size: 24px;
        }
        .user-info {
            font-size: 14px;
            color: #666;
        }
        .content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>➕ Agregar Usuario</h1>
        <div class="user-info">
            Usuario: <strong><?= htmlspecialchars($usuario_actual['username']) ?></strong>
        </div>
    </div>

    <div class="content">
        <?php if ($error): ?>
            <div class="alert alert-error">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-row">
                <div class="form-group">
                    <label>Usuario <span class="required">*</span></label>
                    <input type="text" name="username" required autofocus 
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                           minlength="3">
                    <div class="hint">Mínimo 3 caracteres, sin espacios</div>
                </div>

                <div class="form-group">
                    <label>Nombre Completo <span class="required">*</span></label>
                    <input type="text" name="nombre_completo" required
                           value="<?= htmlspecialchars($_POST['nombre_completo'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <div class="hint">Opcional</div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Contraseña <span class="required">*</span></label>
                    <input type="password" name="password" required minlength="6">
                    <div class="hint">Mínimo 6 caracteres</div>
                </div>

                <div class="form-group">
                    <label>Confirmar Contraseña <span class="required">*</span></label>
                    <input type="password" name="password_confirm" required minlength="6">
                </div>
            </div>

            <div class="form-group">
                <label>Rol del Usuario <span class="required">*</span></label>
                <?php while($r = $roles->fetch_assoc()): ?>
                    <label class="role-option">
                        <input type="radio" name="rol_id" value="<?= $r['id'] ?>" 
                               <?= (isset($_POST['rol_id']) && $_POST['rol_id'] == $r['id']) ? 'checked' : '' ?>
                               <?= (!isset($_POST['rol_id']) && $r['id'] == 2) ? 'checked' : '' ?>
                               required>
                        <strong><?= htmlspecialchars(ucfirst($r['nombre'])) ?></strong>
                        - <?= htmlspecialchars($r['descripcion']) ?>
                    </label>
                <?php endwhile; ?>
            </div>

            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" name="activo" id="activo" 
                           <?= (!isset($_POST['activo']) || isset($_POST['activo'])) ? 'checked' : '' ?>>
                    <label for="activo" style="margin: 0;">Usuario Activo</label>
                </div>
                <div class="hint">Los usuarios inactivos no pueden iniciar sesión</div>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">💾 Crear Usuario</button>
                <a href="usuarios_list.php" class="btn btn-secondary">❌ Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>