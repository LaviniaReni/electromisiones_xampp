<?php
require 'db_connect.php';
require 'auth.php';

// Requerir autenticación
requerir_autenticacion();

$usuario = obtener_usuario_actual();

// Obtener estadísticas
$stats = [];

// Total de productos
$result = $mysqli->query("SELECT COUNT(*) as total FROM productos WHERE activo = 1");
$stats['productos_total'] = $result->fetch_assoc()['total'];

// Productos con stock bajo (menos de 10)
$result = $mysqli->query("SELECT COUNT(*) as total FROM productos WHERE stock < 10 AND activo = 1");
$stats['stock_bajo'] = $result->fetch_assoc()['total'];

// Total de categorías
$result = $mysqli->query("SELECT COUNT(*) as total FROM categorias");
$stats['categorias'] = $result->fetch_assoc()['total'];

// Total de proveedores
$result = $mysqli->query("SELECT COUNT(*) as total FROM proveedores");
$stats['proveedores'] = $result->fetch_assoc()['total'];

// Total de usuarios (solo para admin)
if ($usuario['rol'] === 'admin') {
    $result = $mysqli->query("SELECT COUNT(*) as total FROM usuarios WHERE activo = 1");
    $stats['usuarios_activos'] = $result->fetch_assoc()['total'];
}

// Productos recientes (últimos 5)
$productos_recientes = $mysqli->query("SELECT id, sku, nombre, precio_venta, stock, created_at 
                                       FROM productos 
                                       ORDER BY created_at DESC 
                                       LIMIT 5");

// Productos con stock crítico
$productos_criticos = $mysqli->query("SELECT id, sku, nombre, stock 
                                      FROM productos 
                                      WHERE stock < 10 AND activo = 1
                                      ORDER BY stock ASC 
                                      LIMIT 5");
?>
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='utf-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Electro Misiones</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        .user-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }
        .user-badge {
            background: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
        }
        .btn-logout {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: all 0.3s;
        }
        .btn-logout:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .stat-icon {
            font-size: 40px;
            margin-bottom: 15px;
        }
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        .stat-card.warning .stat-number {
            color: #ff6b6b;
        }
        
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .module-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            border-color: #667eea;
        }
        .module-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .module-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        .module-description {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .content-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 20px;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-danger {
            background: #fee;
            color: #c33;
        }
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .two-column {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .two-column {
                grid-template-columns: 1fr;
            }
            .modules-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔌 Electro Misiones</h1>
        <p>Sistema de Gestión Integral</p>
        <div class="user-info">
            <div class="user-badge">
                👤 <?= htmlspecialchars($usuario['nombre_completo'] ?: $usuario['username']) ?> 
                • <strong><?= htmlspecialchars(strtoupper($usuario['rol'])) ?></strong>
            </div>
            <a href="logout.php" class="btn-logout">🚪 Cerrar Sesión</a>
        </div>
    </div>

    <!-- Estadísticas Rápidas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📦</div>
            <div class="stat-number"><?= $stats['productos_total'] ?></div>
            <div class="stat-label">Productos Activos</div>
        </div>
        
        <div class="stat-card <?= $stats['stock_bajo'] > 0 ? 'warning' : '' ?>">
            <div class="stat-icon">⚠️</div>
            <div class="stat-number"><?= $stats['stock_bajo'] ?></div>
            <div class="stat-label">Stock Bajo</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">🏷️</div>
            <div class="stat-number"><?= $stats['categorias'] ?></div>
            <div class="stat-label">Categorías</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">🏢</div>
            <div class="stat-number"><?= $stats['proveedores'] ?></div>
            <div class="stat-label">Proveedores</div>
        </div>
        
        <?php if ($usuario['rol'] === 'admin'): ?>
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-number"><?= $stats['usuarios_activos'] ?></div>
            <div class="stat-label">Usuarios Activos</div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Módulos Principales -->
    <h2 style="margin-bottom: 20px; color: #333;">📋 Módulos del Sistema</h2>
    <div class="modules-grid">
        <a href="productos_list.php" class="module-card">
            <div class="module-icon">📦</div>
            <div class="module-title">Productos</div>
            <div class="module-description">
                Gestiona el inventario de productos, precios, stock y categorías
            </div>
        </a>
        
        <?php if ($usuario['rol'] === 'admin'): ?>
        <a href="usuarios_list.php" class="module-card">
            <div class="module-icon">👥</div>
            <div class="module-title">Usuarios</div>
            <div class="module-description">
                Administra usuarios, roles y permisos del sistema
            </div>
        </a>
        <?php endif; ?>
        
        <div class="module-card" style="opacity: 0.6; cursor: not-allowed;" title="Próximamente">
            <div class="module-icon">🏷️</div>
            <div class="module-title">Categorías</div>
            <div class="module-description">
                Organiza y gestiona las categorías de productos
                <br><small style="color: #667eea;">🚧 Próximamente</small>
            </div>
        </div>
        
        <div class="module-card" style="opacity: 0.6; cursor: not-allowed;" title="Próximamente">
            <div class="module-icon">🏢</div>
            <div class="module-title">Proveedores</div>
            <div class="module-description">
                Gestiona información de proveedores y contactos
                <br><small style="color: #667eea;">🚧 Próximamente</small>
            </div>
        </div>
        
        <div class="module-card" style="opacity: 0.6; cursor: not-allowed;" title="Próximamente">
            <div class="module-icon">💰</div>
            <div class="module-title">Ventas</div>
            <div class="module-description">
                Registra ventas, genera facturas y controla ingresos
                <br><small style="color: #667eea;">🚧 Próximamente</small>
            </div>
        </div>
        
        <div class="module-card" style="opacity: 0.6; cursor: not-allowed;" title="Próximamente">
            <div class="module-icon">📊</div>
            <div class="module-title">Reportes</div>
            <div class="module-description">
                Visualiza estadísticas, gráficos y reportes detallados
                <br><small style="color: #667eea;">🚧 Próximamente</small>
            </div>
        </div>
    </div>

    <!-- Secciones de Información -->
    <div class="two-column">
        <!-- Productos Recientes -->
        <div class="content-section">
            <h3 class="section-title">📦 Productos Recientes</h3>
            <?php if ($productos_recientes->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($p = $productos_recientes->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($p['sku']) ?></strong></td>
                            <td><?= htmlspecialchars($p['nombre']) ?></td>
                            <td>$<?= number_format($p['precio_venta'], 2, ',', '.') ?></td>
                            <td>
                                <?php if ($p['stock'] < 10): ?>
                                    <span class="badge badge-warning"><?= $p['stock'] ?></span>
                                <?php else: ?>
                                    <?= $p['stock'] ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <p>No hay productos registrados</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Productos con Stock Crítico -->
        <div class="content-section">
            <h3 class="section-title">⚠️ Stock Crítico</h3>
            <?php if ($productos_criticos->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Producto</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($p = $productos_criticos->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($p['sku']) ?></strong></td>
                            <td><?= htmlspecialchars($p['nombre']) ?></td>
                            <td>
                                <span class="badge <?= $p['stock'] < 5 ? 'badge-danger' : 'badge-warning' ?>">
                                    <?= $p['stock'] ?> unidades
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">✅</div>
                    <p>Todos los productos tienen stock adecuado</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>