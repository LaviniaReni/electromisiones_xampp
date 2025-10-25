<?php
require 'db_connect.php';
$result = $mysqli->query("SELECT p.id, p.sku, p.nombre, p.precio_venta, p.stock, c.nombre AS categoria FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id ORDER BY p.id DESC");
?>
<!doctype html>
<html lang='es'>
<head><meta charset='utf-8'><title>Productos - Electro Misiones (Demo)</title>
<style>table{border-collapse:collapse;width:100%}td,th{border:1px solid #ccc;padding:8px}</style>
</head>
<body>
<h1>Productos</h1>
<p><a href='producto_add.php'>Agregar producto</a></p>
<table>
<tr><th>ID</th><th>SKU</th><th>Nombre</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Acciones</th></tr>
<?php while($r=$result->fetch_assoc()): ?>
<tr>
<td><?=htmlspecialchars($r['id'])?></td>
<td><?=htmlspecialchars($r['sku'])?></td>
<td><?=htmlspecialchars($r['nombre'])?></td>
<td><?=htmlspecialchars($r['categoria'])?></td>
<td><?=number_format($r['precio_venta'],2,',','.')?></td>
<td><?=htmlspecialchars($r['stock'])?></td>
<td><a href='producto_edit.php?id=<?=$r['id']?>'>Editar</a> | <a href='producto_delete.php?id=<?=$r['id']?>' onclick="return confirm('Borrar este producto?')">Borrar</a></td>
</tr>
<?php endwhile; ?>
</table>
</body>
</html>