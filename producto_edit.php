<?php
require 'db_connect.php';
$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: productos_list.php'); exit; }
$stmt = $mysqli->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->bind_param("i",$id); $stmt->execute(); $res = $stmt->get_result(); $prod = $res->fetch_assoc();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sku = $_POST['sku'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $categoria_id = $_POST['categoria_id'] ?: null;
    $proveedor_id = $_POST['proveedor_id'] ?: null;
    $precio_compra = $_POST['precio_compra'] ?: 0;
    $precio_venta = $_POST['precio_venta'] ?: 0;
    $stock = $_POST['stock'] ?: 0;
    $u = $mysqli->prepare("UPDATE productos SET sku=?, nombre=?, categoria_id=?, proveedor_id=?, precio_compra=?, precio_venta=?, stock=? WHERE id=?");
    $u->bind_param("ssiddiii", $sku, $nombre, $categoria_id, $proveedor_id, $precio_compra, $precio_venta, $stock, $id);
    $u->execute();
    header('Location: productos_list.php'); exit;
}
$cat_res = $mysqli->query("SELECT id, nombre FROM categorias");
$prov_res = $mysqli->query("SELECT id, nombre FROM proveedores");
?>
<!doctype html><html lang='es'><head><meta charset='utf-8'><title>Editar Producto</title></head><body>
<h1>Editar Producto</h1>
<form method='post'>
<label>SKU:<br><input name='sku' value="<?=htmlspecialchars($prod['sku'])?>" required></label><br>
<label>Nombre:<br><input name='nombre' value="<?=htmlspecialchars($prod['nombre'])?>" required></label><br>
<label>Categoría:<br>
<select name='categoria_id'><option value=''>-- sin categoria --</option>
<?php while($c=$cat_res->fetch_assoc()): ?><option value="<?=$c['id']?>" <?php if($prod['categoria_id']==$c['id']) echo 'selected'; ?>><?=htmlspecialchars($c['nombre'])?></option><?php endwhile; ?>
</select></label><br>
<label>Proveedor:<br>
<select name='proveedor_id'><option value=''>-- sin proveedor --</option>
<?php while($p=$prov_res->fetch_assoc()): ?><option value="<?=$p['id']?>" <?php if($prod['proveedor_id']==$p['id']) echo 'selected'; ?>><?=htmlspecialchars($p['nombre'])?></option><?php endwhile; ?>
</select></label><br>
<label>Precio compra:<br><input name='precio_compra' type='number' step='0.01' value="<?=htmlspecialchars($prod['precio_compra'])?>"></label><br>
<label>Precio venta:<br><input name='precio_venta' type='number' step='0.01' value="<?=htmlspecialchars($prod['precio_venta'])?>"></label><br>
<label>Stock:<br><input name='stock' type='number' value="<?=htmlspecialchars($prod['stock'])?>"></label><br>
<button type='submit'>Actualizar</button>
</form>
<p><a href='productos_list.php'>Volver a lista</a></p>
</body></html>