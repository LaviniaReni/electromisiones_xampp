<?php
require 'db_connect.php';
require 'auth.php';

// Si ya está autenticado, redirigir al inicio
if (esta_autenticado()) {
    header('Location: index.php');
    exit;
}

// Verificar si ya existen usuarios en el sistema
$check_usuarios = $mysqli->query("SELECT COUNT(*) as total FROM usuarios");
$resultado = $check_usuarios->fetch_assoc();
$hay_usuarios = $resultado['total'] > 0;

// Si ya hay usuarios, redirigir al login
if ($hay_usuarios) {
    header('Location: login.php?registro_deshabilitado=1');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $nombre_completo = trim($_POST['nombre_completo'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
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
    } else {
        // Verificar nuevamente que no se haya creado otro usuario mientras tanto
        $check_again = $mysqli->query("SELECT COUNT(*) as total FROM usuarios");
        $resultado_again = $check_again->fetch_assoc();
        
        if ($resultado_again['total'] > 0) {
            $error = 'Ya existe un usuario en el sistema. El registro ha sido deshabilitado.';
        } else {
            try {
                // Crear el hash de la contraseña
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Insertar el primer usuario como ADMIN (rol_id = 1)
                $stmt = $mysqli->prepare("INSERT INTO usuarios (username, password_hash, nombre_completo, email, rol_id, activo) VALUES (?, ?, ?, ?, 1, 1)");
                $stmt->bind_param("ssss", $username, $password_hash, $nombre_completo, $email);
                
                if ($stmt->execute()) {
                    // Login automático después del registro
                    $resultado_login = login_usuario($mysqli, $username, $password);
                    
                    if ($resultado_login['success']) {
                        header('Location: productos_list.php?primer_usuario=1');
                        exit;
                    } else {
                        $success = 'Usuario creado exitosamente. Ahora puedes iniciar sesión.';
                        // Redirigir al login después de 2 segundos
                        header("refresh:2;url=login.php");
                    }
                } else {
                    $error = 'Error al crear el usuario';
                }
            } catch (Exception $e) {
                if ($mysqli->errno == 1062) {
                    $error = 'El usuario o email ya existe';
                } else {
                    $error = 'Error al registrar: ' . $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='utf-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro Inicial - Electro Misiones</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .register-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 500px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            text-align: center;
        }
        .subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .info-box {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            color: #004085;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 25px;
            font-size: 13px;
            line-height: 1.5;
        }
        .info-box strong {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        label .required {
            color: #ff6b6b;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        .password-hint {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #5568d3;
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
        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h1>🔌 Electro Misiones</h1>
        <p class="subtitle">Configuración Inicial del Sistema</p>
        
        <div class="info-box">
            <strong>👤 Crear Primer Usuario Administrador</strong>
            Este es el primer acceso al sistema. Crea tu cuenta de administrador para comenzar. Esta opción solo estará disponible una vez.
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                ✅ <?= htmlspecialchars($success) ?>
                <br>Redirigiendo al login...
            </div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label>Usuario <span class="required">*</span></label>
                <input type="text" name="username" required autofocus 
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                       minlength="3">
                <div class="password-hint">Mínimo 3 caracteres</div>
            </div>
            
            <div class="form-group">
                <label>Nombre Completo <span class="required">*</span></label>
                <input type="text" name="nombre_completo" required
                       value="<?= htmlspecialchars($_POST['nombre_completo'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <div class="password-hint">Opcional</div>
            </div>
            
            <div class="form-group">
                <label>Contraseña <span class="required">*</span></label>
                <input type="password" name="password" required minlength="6">
                <div class="password-hint">Mínimo 6 caracteres</div>
            </div>

            <div class="form-group">
                <label>Confirmar Contraseña <span class="required">*</span></label>
                <input type="password" name="password_confirm" required minlength="6">
            </div>
            
            <button type="submit">🚀 Crear Cuenta de Administrador</button>
        </form>

        <div class="login-link">
            ¿Ya tienes una cuenta? <a href="login.php">Iniciar Sesión</a>
        </div>
    </div>
</body>
</html>