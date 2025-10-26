<?php
require 'auth.php';
requerir_autenticacion();
$usuario = obtener_usuario_actual();
?>
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='utf-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso Denegado</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
        }
        .error-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        h1 {
            color: #c33;
            margin-bottom: 10px;
        }
        p {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        .user-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">🚫</div>
        <h1>Acceso Denegado</h1>
        <p>No tienes permisos suficientes para acceder a esta página.</p>
        
        <div class="user-info">
            <strong>Usuario actual:</strong> <?= htmlspecialchars($usuario['username']) ?><br>
            <strong>Rol:</strong> <?= htmlspecialchars($usuario['rol']) ?>
        </div>
        
        <p>Si crees que deberías tener acceso, contacta al administrador del sistema.</p>
        
        <a href="index.php" class="btn">Volver al Inicio</a>
    </div>
</body>
</html>