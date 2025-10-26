<?php
require 'db_connect.php';
require 'auth.php';

// Solo admin y vendedor pueden editar productos
requerir_permiso(['admin', 'vendedor']);

$usuario = obtener_usuario_actual();
$id = intval($_GET['id'] ?? 0);

if (!$id) {
    header('Location: productos_list.php');
    exit;
}

$stmt = $mysqli->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$prod = $res->fetch_assoc();

if (!$prod) {
    $_SESSION['mensaje_error'] = 'Producto no encontrado';
    header('Location: productos_list.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sku = trim($_POST['sku'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $categoria_id = $_POST['categoria_id'] ?: null;
    $proveedor_id = $_POST['proveedor_id'] ?: null;
    $precio_compra = $_POST['precio_compra'] ?: 0;
    $precio_venta = $_POST['precio_venta'] ?: 0;
    $stock = $_POST['stock'] ?: 0;
    
    if (empty($sku) || empty($nombre)) {
        $error = 'SKU y Nombre son obligatorios';
    } else {
        try {
            $u = $mysqli->prepare("UPDATE productos SET sku=?, nombre=?, categoria_id=?, proveedor_id=?, precio_compra=?, precio_venta=?, stock=? WHERE id=?");
            $u->bind_param("ssiddiii", $sku, $nombre, $categoria_id, $proveedor_id, $precio_compra, $precio_venta, $stock, $id);
            
            if ($u->execute()) {
                $_SESSION['mensaje_exito'] = 'Producto actualizado exitosamente';
                header('Location: productos_list.php');
                exit;
            } else {
                $error = 'Error al actualizar el producto';
            }
        } catch (Exception $e) {
            if ($mysqli->errno == 1062) {
                $error = 'El SKU ya existe en otro producto';
            } else {
                $error = 'Error al actualizar: ' . $e->getMessage();
            }
        }
    }
}

$cat_res = $mysqli->query("SELECT id, nombre FROM categorias ORDER BY nombre");
$prov_res = $mysqli->query("SELECT id, nombre FROM proveedores ORDER BY nombre");
?>
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='utf-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Producto - Electro Misiones</title>
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
        input[type="text"],
        input[type="number"],
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
        .required {
            color: #ff6b6b;
        }
        .product-id {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>✏️ Editar Producto</h1>
        <div class="user-info">
            Usuario: <strong><?= htmlspecialchars($usuario['username']) ?></strong>
        </div>
    </div>

    <div class="content">
        <div class="product-id">
            📦 Editando producto ID: <strong><?= htmlspecialchars($prod['id']) ?></strong>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label>SKU <span class="required">*</span></label>
                <input type="text" name="sku" required value="<?= htmlspecialchars($prod['sku']) ?>">
            </div>

            <div class="form-group">
                <label>Nombre del Producto <span class="required">*</span></label>
                <input type="text" name="nombre" required value="<?= htmlspecialchars($prod['nombre']) ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Categoría</label>
                    <select name="categoria_id">
                        <option value="">-- Sin categoría --</option>
                        <?php while($c = $cat_res->fetch_assoc()): ?>
                            <option value="<?= $c['id'] ?>" <?= ($prod['categoria_id'] == $c['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nombre']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Proveedor</label>
                    <select name="proveedor_id">
                        <option value="">-- Sin proveedor --</option>
                        <?php while($p = $prov_res->fetch_assoc()): ?>
                            <option value="<?= $p['id'] ?>" <?= ($prod['proveedor_id'] == $p['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['nombre']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Precio de Compra</label>
                    <input type="number" name="precio_compra" step="0.01" min="0" value="<?= htmlspecialchars($prod['precio_compra']) ?>">
                </div>

                <div class="form-group">
                    <label>Precio de Venta</label>
                    <input type="number" name="precio_venta" step="0.01" min="0" value="<?= htmlspecialchars($prod['precio_venta']) ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Stock</label>
                <input type="number" name="stock" min="0" value="<?= htmlspecialchars($prod['stock']) ?>">
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">💾 Actualizar Producto</button>
                <a href="productos_list.php" class="btn btn-secondary">❌ Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>