<?php
/**
 * Comidas XYZ - Gestión de productos (interfaz web, rol admin)
 * RF-01 a RF-04: registrar, consultar, modificar, eliminar y calcular IVA.
 */
require 'config.php';
requiere_rol('admin');
$pdo = conectar();

$mensaje = '';
$prod = ['id_producto'=>'','codigo_unico'=>'','nombre'=>'','descripcion'=>'',
         'tiempo_prep_min'=>'','costo_antes_iva'=>'','porcentaje_iva'=>'','costo_despues_iva'=>''];

// Eliminar
if (isset($_GET['eliminar'])) {
    $stmt = $pdo->prepare('DELETE FROM productos WHERE id_producto = ?');
    $stmt->execute([(int)$_GET['eliminar']]);
    $mensaje = 'Producto eliminado.';
}

// Editar (cargar datos)
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare('SELECT * FROM productos WHERE id_producto = ?');
    $stmt->execute([(int)$_GET['editar']]);
    $prod = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Crear / actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id      = $_POST['id_producto'] ? (int)$_POST['id_producto'] : null;
    $codigo  = trim($_POST['codigo_unico']);
    $nombre  = trim($_POST['nombre']);
    $desc    = trim($_POST['descripcion']);
    $tiempo  = (int)$_POST['tiempo_prep_min'];
    $antes   = (float)$_POST['costo_antes_iva'];
    $porc    = (float)$_POST['porcentaje_iva'];
    $despues = $antes * (1 + $porc / 100); // cálculo del IVA

    if ($id) {
        $sql = 'UPDATE productos SET codigo_unico=?, nombre=?, descripcion=?, tiempo_prep_min=?,
                costo_antes_iva=?, porcentaje_iva=?, costo_despues_iva=? WHERE id_producto=?';
        $pdo->prepare($sql)->execute([$codigo,$nombre,$desc,$tiempo,$antes,$porc,$despues,$id]);
        $mensaje = 'Producto actualizado.';
    } else {
        $sql = 'INSERT INTO productos
                (codigo_unico, nombre, descripcion, tiempo_prep_min,
                 costo_antes_iva, porcentaje_iva, costo_despues_iva)
                VALUES (?,?,?,?,?,?,?)';
        $pdo->prepare($sql)->execute([$codigo,$nombre,$desc,$tiempo,$antes,$porc,$despues]);
        $mensaje = 'Producto creado.';
    }
}

$productos = $pdo->query('SELECT * FROM productos ORDER BY nombre')->fetchAll(PDO::FETCH_ASSOC);

encabezado('Productos');
if ($mensaje) echo '<p class="ok">' . e($mensaje) . '</p>';
?>

<form method="post">
  <input type="hidden" name="id_producto" value="<?= e($prod['id_producto']) ?>">
  <label>Código único</label><input type="text" name="codigo_unico" value="<?= e($prod['codigo_unico']) ?>" required>
  <label>Nombre</label><input type="text" name="nombre" value="<?= e($prod['nombre']) ?>" required>
  <label>Descripción</label><input type="text" name="descripcion" value="<?= e($prod['descripcion']) ?>">
  <label>Tiempo prep. (min)</label><input type="number" name="tiempo_prep_min" value="<?= e($prod['tiempo_prep_min']) ?>">
  <label>Costo antes IVA</label><input type="number" step="0.01" name="costo_antes_iva" value="<?= e($prod['costo_antes_iva']) ?>" required>
  <label>% IVA</label><input type="number" step="0.01" name="porcentaje_iva" value="<?= e($prod['porcentaje_iva']) ?>" required>
  <label>Costo después IVA</label><input type="text" value="<?= $prod['costo_despues_iva'] ?>" disabled title="Se calcula automáticamente">
  <br><button class="btn" type="submit">Guardar</button>
</form>

<table>
  <tr><th>ID</th><th>Código</th><th>Nombre</th><th>Antes IVA</th><th>% IVA</th><th>Después IVA</th><th>Tiempo</th><th>Acciones</th></tr>
  <?php foreach ($productos as $p): ?>
  <tr>
    <td><?= e($p['id_producto']) ?></td>
    <td><?= e($p['codigo_unico']) ?></td>
    <td><?= e($p['nombre']) ?></td>
    <td><?= number_format($p['costo_antes_iva'], 2) ?></td>
    <td><?= e($p['porcentaje_iva']) ?></td>
    <td><?= number_format($p['costo_despues_iva'], 2) ?></td>
    <td><?= e($p['tiempo_prep_min']) ?></td>
    <td><a href="productos.php?editar=<?= $p['id_producto'] ?>">Editar</a> |
        <a href="productos.php?eliminar=<?= $p['id_producto'] ?>" onclick="return confirm('¿Eliminar?')">Eliminar</a></td>
  </tr>
  <?php endforeach; ?>
</table>
</body></html>
