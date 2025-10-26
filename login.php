<?php
require 'db_connect.php';
require 'auth.php';

// Si ya está autenticado, redirigir al inicio
if (esta_autenticado()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

// Verificar si existen usuarios en el sistema
$check_usuarios = $mysqli->query("SELECT COUNT(*) as total FROM usuarios");
$resultado_check = $check_usuarios->fetch_assoc();
$hay_usuarios = $resultado_check['total'] > 0;

// Mensaje si intentaron acceder a registro cuando ya hay usuarios
if (isset($_GET['registro_deshabilitado'])) {
    $error = 'El registro ya no está disponible. Ya existe al menos un usuario en el sistema.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Por favor complete todos los campos';
    } else {
        $resultado = login_usuario($mysqli, $username, $password);
        
        if ($resultado['success']) {
            $success = $resultado['mensaje'];
            
            // Redirigir a la página solicitada o al inicio
            $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
            unset($_SESSION['redirect_after_login']);
            
            header("Location: $redirect");
            exit;
        } else {
            $error = $resultado['mensaje'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='utf-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Electro Misiones</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            text-align: center;
        }
        .subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 30px;
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
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
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
        .demo-info {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            font-size: 13px;
            color: #666;
        }
        .demo-info h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .demo-info code {
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>🔌 Electro Misiones</h1>
        <p class="subtitle">Sistema de Gestión</p>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label>Usuario</label>
                <input type="text" name="username" required autofocus>
            </div>
            
            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit">Iniciar Sesión</button>
        </form>
        
        <div class="demo-info">
            <h3>👤 Usuarios de prueba:</h3>
            <p><strong>Administrador:</strong></p>
            <p>Usuario: <code>admin</code> / Contraseña: <code>admin123</code></p>
            <br>
            <p><strong>Vendedor:</strong></p>
            <p>Usuario: <code>vendedor</code> / Contraseña: <code>vendedor123</code></p>
        </div>
    </div>
</body>
</html>