<?php
require 'db_connect.php';
require 'auth.php';

// Solo admin puede gestionar usuarios
requerir_rol('admin');

$usuario = obtener_usuario_actual();

// Obtener lista de usuarios con sus roles
$result = $mysqli->query("SELECT u.id, u.username, u.nombre_completo, u.email, u.activo, u.ultimo_acceso, u.created_at, r.nombre as rol 
                          FROM usuarios u 
                          LEFT JOIN roles r ON u.rol_id = r.id 
                          ORDER BY u.created_at DESC");

// Capturar mensajes
$mensaje_exito = $_SESSION['mensaje_exito'] ?? '';
$mensaje_error = $_SESSION['mensaje_error'] ?? '';
unset($_SESSION['mensaje_exito'], $_SESSION['mensaje_error']);
?>
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='utf-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Usuarios - Electro Misiones</title>
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
        .nav-links {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            width: 100%;
        }
        .nav-links a {
            padding: 8px 16px;
            background: #f8f9fa;
            color: #333;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            transition: all 0.3s;
        }
        .nav-links a:hover {
            background: #e9ecef;
        }
        .nav-links a.active {
            background: #667eea;
            color: white;
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
        .btn {
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
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
        .btn-logout {
            background: #ff6b6b;
            color: white;
        }
        .btn-logout:hover {
            background: #ee5a52;
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
        .status-badge {
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
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
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            font-size: 14px;
            margin-bottom: 10px;
            opacity: 0.9;
        }
        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
        }
        .current-user {
            background: #fff3cd;
            border: 1px solid #ffc107;
        }
        small {
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div style="flex: 1;">
            <h1>👥 Gestión de Usuarios</h1>
            <div class="nav-links">
                <a href="productos_list.php">📦 Productos</a>
                <a href="usuarios_list.php" class="active">👥 Usuarios</a>
            </div>
        </div>
        <div class="user-info">
            <span class="user-badge"><?= htmlspecialchars($usuario['username']) ?></span>
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

        <?php
        // Calcular estadísticas
        $total = 0;
        $activos = 0;
        $admins = 0;
        $vendedores = 0;
        $visores = 0;
        
        $result->data_seek(0);
        while ($u = $result->fetch_assoc()) {
            $total++;
            if ($u['activo']) $activos++;
            if ($u['rol'] === 'admin') $admins++;
            if ($u['rol'] === 'vendedor') $vendedores++;
            if ($u['rol'] === 'visor') $visores++;
        }
        $result->data_seek(0);
        ?>

        <div class="stats">
            <div class="stat-card">
                <h3>Total Usuarios</h3>
                <div class="number"><?= $total ?></div>
            </div>
            <div class="stat-card">
                <h3>Usuarios Activos</h3>
                <div class="number"><?= $activos ?></div>
            </div>
            <div class="stat-card">
                <h3>Administradores</h3>
                <div class="number"><?= $admins ?></div>
            </div>
            <div class="stat-card">
                <h3>Vendedores</h3>
                <div class="number"><?= $vendedores ?></div>
            </div>
        </div>

        <div class="actions">
            <a href="usuario_add.php" class="btn btn-primary">➕ Agregar Usuario</a>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Último Acceso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($u = $result->fetch_assoc()): ?>
                    <tr <?= ($u['id'] == $usuario['id']) ? 'class="current-user"' : '' ?>>
                        <td><?= htmlspecialchars($u['id']) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($u['username']) ?></strong>
                            <?php if ($u['id'] == $usuario['id']): ?>
                                <br><small>(Tú)</small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($u['nombre_completo']) ?></td>
                        <td><?= htmlspecialchars($u['email'] ?: '-') ?></td>
                        <td>
                            <span class="role-badge role-<?= htmlspecialchars($u['rol']) ?>">
                                <?= htmlspecialchars(strtoupper($u['rol'])) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($u['activo']): ?>
                                <span class="status-badge status-active">Activo</span>
                            <?php else: ?>
                                <span class="status-badge status-inactive">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($u['ultimo_acceso']): ?>
                                <?= date('d/m/Y H:i', strtotime($u['ultimo_acceso'])) ?>
                            <?php else: ?>
                                <small>Nunca</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href='usuario_edit.php?id=<?= $u['id'] ?>' class="btn btn-edit">✏️ Editar</a>
                            
                            <?php if ($u['id'] != $usuario['id']): ?>
                                <a href='usuario_delete.php?id=<?= $u['id'] ?>' 
                                   class="btn btn-delete" 
                                   onclick="return confirm('¿Eliminar al usuario <?= htmlspecialchars($u['username']) ?>?\n\nEsta acción no se puede deshacer.')">
                                    🗑️ Eliminar
                                </a>
                            <?php else: ?>
                                <span style="color: #999; font-size: 13px;">No puedes eliminarte</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                <p>No hay usuarios registrados</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>