<?php
// reset_usuarios.php
// Script para resetear usuarios y habilitar registro nuevamente
// ⚠️ USAR SOLO EN DESARROLLO - ELIMINAR EN PRODUCCIÓN

require 'db_connect.php';

$accion_ejecutada = false;
$mensaje = '';

if (isset($_GET['confirmar']) && $_GET['confirmar'] === 'si_estoy_seguro') {
    try {
        // Eliminar todas las sesiones
        $mysqli->query("DELETE FROM sesiones");
        
        // Eliminar todos los usuarios
        $mysqli->query("DELETE FROM usuarios");
        
        // Reiniciar el auto_increment
        $mysqli->query("ALTER TABLE usuarios AUTO_INCREMENT = 1");
        
        $accion_ejecutada = true;
        $mensaje = 'Todos los usuarios han sido eliminados. Ahora puedes crear un nuevo usuario administrador.';
    } catch (Exception $e) {
        $mensaje = 'Error al resetear: ' . $e->getMessage();
    }
}

// Contar usuarios actuales
$result = $mysqli->query("SELECT COUNT(*) as total FROM usuarios");
$row = $result->fetch_assoc();
$total_usuarios = $row['total'];
?>
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='utf-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resetear Sistema - Electro Misiones</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .warning {
            background: #fff3cd;
            border: 2px solid #ffc107;
            color: #856404;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .warning h2 {
            color: #856404;
            margin-bottom: 10px;
        }
        .danger {
            background: #f8d7da;
            border: 2px solid #dc3545;
            color: #721c24;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .danger h2 {
            color: #721c24;
            margin-bottom: 10px;
        }
        .success {
            background: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .success h2 {
            color: #155724;
            margin-bottom: 10px;
        }
        .info-box {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            color: #004085;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
            transition: all 0.3s;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
        }
        ul {
            margin-left: 20px;
            margin-top: 10px;
            line-height: 1.8;
        }
        .actions {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Resetear Sistema de Usuarios</h1>
        
        <?php if ($accion_ejecutada): ?>
            <div class="success">
                <h2>✅ Sistema Reseteado</h2>
                <p><?= htmlspecialchars($mensaje) ?></p>
            </div>
            
            <div class="actions">
                <a href="registro.php" class="btn btn-primary">Crear Nuevo Usuario Administrador</a>
                <a href="login.php" class="btn btn-secondary">Ir al Login</a>
            </div>
        <?php else: ?>
            <div class="info-box">
                <strong>📊 Estado Actual:</strong><br>
                Usuarios en el sistema: <strong><?= $total_usuarios ?></strong>
            </div>

            <?php if ($total_usuarios === 0): ?>
                <div class="success">
                    <h2>✅ No hay usuarios en el sistema</h2>
                    <p>El sistema está listo para crear el primer usuario administrador.</p>
                </div>
                
                <div class="actions">
                    <a href="registro.php" class="btn btn-primary">Crear Primer Usuario</a>
                    <a href="login.php" class="btn btn-secondary">Ir al Login</a>
                </div>
            <?php else: ?>
                <div class="warning">
                    <h2>⚠️ Advertencia</h2>
                    <p>Esta herramienta es solo para desarrollo y pruebas.</p>
                </div>

                <div class="danger">
                    <h2>⛔ PELIGRO: Acción Irreversible</h2>
                    <p><strong>Esta acción eliminará:</strong></p>
                    <ul>
                        <li>Todos los usuarios del sistema (<?= $total_usuarios ?> usuarios)</li>
                        <li>Todas las sesiones activas</li>
                        <li>Toda la configuración de autenticación</li>
                    </ul>
                    <p style="margin-top: 15px;">
                        <strong>⚠️ Los productos, categorías y proveedores NO serán eliminados.</strong>
                    </p>
                    <p style="margin-top: 15px;">
                        <strong>Después de resetear, podrás crear un nuevo usuario administrador desde cero.</strong>
                    </p>
                </div>

                <div class="actions">
                    <a href="?confirmar=si_estoy_seguro" 
                       class="btn btn-danger" 
                       onclick="return confirm('⚠️ ¿ESTÁS COMPLETAMENTE SEGURO?\n\nEsta acción NO se puede deshacer.\n\nSe eliminarán todos los usuarios del sistema.\n\n¿Continuar?')">
                        🗑️ Eliminar Todos los Usuarios
                    </a>
                    <a href="login.php" class="btn btn-secondary">Cancelar</a>
                </div>

                <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px;">
                    <h3>💡 Alternativa más segura:</h3>
                    <p>En lugar de resetear, puedes:</p>
                    <ol style="margin-left: 20px; margin-top: 10px; line-height: 1.8;">
                        <li>Usar el archivo <code>generar_password.php</code> para generar nuevas contraseñas</li>
                        <li>Actualizar las contraseñas en phpMyAdmin manualmente</li>
                        <li>O crear un nuevo usuario ejecutando SQL en phpMyAdmin</li>
                    </ol>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div style="margin-top: 40px; padding: 15px; background: #fff3cd; border-radius: 5px; font-size: 14px;">
            <strong>⚠️ IMPORTANTE PARA PRODUCCIÓN:</strong><br>
            ELIMINA este archivo (<code>reset_usuarios.php</code>) antes de poner el sistema en producción.
            Es una herramienta peligrosa que no debe estar disponible en un entorno real.
        </div>
    </div>
</body>
</html>