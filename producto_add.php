<?php
require 'db_connect.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $sku = $_POST['sku'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $categoria_id = $_POST['categoria_id'] ?: null;
    $proveedor_id = $_POST['proveedor_id'] ?: null;
    $precio_compra = $_POST['precio_compra'] ?: 0;
    $precio_venta = $_POST['precio_venta'] ?: 0;
    $stock = $_POST['stock'] ?: 0;
    $stmt = $mysqli->prepare("INSERT INTO productos (sku,nombre,categoria_id,proveedor_id,precio_compra,precio_venta,stock) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param("ssiddii", $sku, $nombre, $categoria_id, $proveedor_id, $precio_compra, $precio_venta, $stock);
    $stmt->execute();
    header('Location: productos_list.php'); exit;
}
$cats = $mysqli->query("SELECT id,nombre FROM categorias");
$provs = $mysqli->query("SELECT id,nombre FROM proveedores");
?>
<!doctype html><html lang='es'><head><meta charset='utf-8'><title>Agregar Producto</title></head><body>
<h1>Agregar Producto</h1>
<form method='post'>
<label>SKU:<br><input name='sku' required></label><br>
<label>Nombre:<br><input name='nombre' required></label><br>
<label>Categoría:<br>
<select name='categoria_id'><option value=''>-- sin categoria --</option>
<?php while($c=$cats->fetch_assoc()): ?><option value="<?=$c['id']?>"><?=htmlspecialchars($c['nombre'])?></option><?php endwhile; ?>
</select></label><br>
<label>Proveedor:<br>
<select name='proveedor_id'><option value=''>-- sin proveedor --</option>
<?php while($p=$provs->fetch_assoc()): ?><option value="<?=$p['id']?>"><?=htmlspecialchars($p['nombre'])?></option><?php endwhile; ?>
</select></label><br>
<label>Precio compra:<br><input name='precio_compra' type='number' step='0.01'></label><br>
<label>Precio venta:<br><input name='precio_venta' type='number' step='0.01'></label><br>
<label>Stock:<br><input name='stock' type='number' value='0'></label><br>
<button type='submit'>Guardar</button>
</form>
<p><a href='productos_list.php'>Volver a lista</a></p>
</body></html>