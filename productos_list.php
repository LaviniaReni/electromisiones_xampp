<?php
require 'db_connect.php';
require 'auth.php';

// Requerir autenticación - cualquier usuario autenticado puede ver productos
requerir_autenticacion();

$usuario = obtener_usuario_actual();
$puede_editar = puede_editar();
$puede_eliminar = puede_eliminar();

$result = $mysqli->query("SELECT p.id, p.sku, p.nombre, p.precio_venta, p.stock, c.nombre AS categoria 
                          FROM productos p 
                          LEFT JOIN categorias c ON p.categoria_id = c.id 
                          ORDER BY p.id DESC");

// Capturar mensajes de sesión
$mensaje_exito = $_SESSION['mensaje_exito'] ?? '';
$mensaje_error = $_SESSION['mensaje_error'] ?? '';
unset($_SESSION['mensaje_exito'], $_SESSION['mensaje_error']);
?>
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='utf-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Productos - Electro Misiones</title>
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
            flex-wrap: wrap;
            gap: 15px;
        }
        .header h1 {
            color: #333;
            font-size: 24px;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 14px;
        }
        .user-badge {
            padding: 6px 12px;
            background: #667eea;
            color: white;
            border-radius: 20px;
            font-weight: bold;
        }
        .role-badge {
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }
        .role-admin { background: #ff6b6b; color: white; }
        .role-vendedor { background: #4ecdc4; color: white; }
        .role-visor { background: #95e1d3; color: #333; }
        .btn {
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
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
        .btn-logout {
            background: #ff6b6b;
            color: white;
        }
        .btn-logout:hover {
            background: #ee5a52;
        }
        .btn-edit {
            background: #4ecdc4;
            color: white;
            padding: 6px 12px;
            font-size: 13px;
        }
        .btn-delete {
            background: #ff6b6b;
            color: white;
            padding: 6px 12px;
            font-size: 13px;
        }
        .btn-disabled {
            background: #ccc;
            color: #666;
            cursor: not-allowed;
        }
        .content {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .actions {
            margin-bottom: 20px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background: #667eea;
            color: white;
            font-weight: bold;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        .permission-note {
            background: #fff3cd;
            color: #856404;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔌 Electro Misiones - Productos</h1>
        <div class="user-info">
            <span class="user-badge"><?= htmlspecialchars($usuario['nombre_completo'] ?: $usuario['username']) ?></span>
            <span class="role-badge role-<?= htmlspecialchars($usuario['rol']) ?>">
                <?= htmlspecialchars(strtoupper($usuario['rol'])) ?>
            </span>
            <a href="logout.php" class="btn btn-logout">Cerrar Sesión</a>
        </div>
    </div>

    <div class="content">
        <?php if ($mensaje_exito): ?>
            <div class="alert alert-success">✅ <?= htmlspecialchars($mensaje_exito) ?></div>
        <?php endif; ?>

        <?php if ($mensaje_error): ?>
            <div class="alert alert-error">❌ <?= htmlspecialchars($mensaje_error) ?></div>
        <?php endif; ?>

        <?php if ($usuario['rol'] === 'visor'): ?>
            <div class="permission-note">
                ℹ️ Tienes permisos de <strong>solo lectura</strong>. No puedes agregar, editar o eliminar productos.
            </div>
        <?php endif; ?>

        <div class="actions">
            <?php if ($puede_editar): ?>
                <a href='producto_add.php' class="btn btn-primary">➕ Agregar Producto</a>
            <?php else: ?>
                <span class="btn btn-disabled" title="No tienes permisos">➕ Agregar Producto</span>
            <?php endif; ?>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>SKU</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($r = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['id']) ?></td>
                        <td><?= htmlspecialchars($r['sku']) ?></td>
                        <td><?= htmlspecialchars($r['nombre']) ?></td>
                        <td><?= htmlspecialchars($r['categoria']) ?></td>
                        <td>$<?= number_format($r['precio_venta'], 2, ',', '.') ?></td>
                        <td><?= htmlspecialchars($r['stock']) ?></td>
                        <td>
                            <?php if ($puede_editar): ?>
                                <a href='producto_edit.php?id=<?= $r['id'] ?>' class="btn btn-edit">Editar</a>
                            <?php endif; ?>
                            
                            <?php if ($puede_eliminar): ?>
                                <a href='producto_delete.php?id=<?= $r['id'] ?>' 
                                   class="btn btn-delete" 
                                   onclick="return confirm('¿Borrar este producto?')">Borrar</a>
                            <?php endif; ?>
                            
                            <?php if (!$puede_editar && !$puede_eliminar): ?>
                                <span style="color: #999; font-size: 13px;">Ver solo</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                <p>No hay productos registrados</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>