<?php
/**
 * Comidas XYZ - Gestión de menús (interfaz web, rol admin)
 * RF-05 a RF-07: crear menú por día y agregar varios productos (N:M).
 */
require 'config.php';
requiere_rol('admin');
$pdo = conectar();

$mensaje = '';
$dias = ['Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo'];

if (isset($_GET['eliminar'])) {
    $pdo->prepare('DELETE FROM menus WHERE id_menu = ?')->execute([(int)$_GET['eliminar']]);
    $mensaje = 'Menú eliminado.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descripcion = trim($_POST['descripcion']);
    $dia         = $_POST['dia_semana'];
    $productos   = $_POST['productos'] ?? []; // arreglo de id_producto

    $pdo->prepare('INSERT INTO menus (descripcion, dia_semana) VALUES (?, ?)')
        ->execute([$descripcion, $dia]);
    $id_menu = $pdo->lastInsertId();

    $ins = $pdo->prepare('INSERT INTO menu_productos (id_menu, id_producto) VALUES (?, ?)');
    foreach ($productos as $pid) {
        $ins->execute([$id_menu, (int)$pid]);
    }
    $mensaje = 'Menú creado con ' . count($productos) . ' producto(s).';
}

$productos = $pdo->query('SELECT * FROM productos ORDER BY nombre')->fetchAll(PDO::FETCH_ASSOC);
$menus = $pdo->query('
    SELECT m.*, COUNT(mp.id_producto) AS total
    FROM menus m
    LEFT JOIN menu_productos mp ON mp.id_menu = m.id_menu
    GROUP BY m.id_menu
    ORDER BY m.id_menu DESC
')->fetchAll(PDO::FETCH_ASSOC);

encabezado('Menús');
if ($mensaje) echo '<p class="ok">' . e($mensaje) . '</p>';
?>

<form method="post">
  <label>Descripción</label><input type="text" name="descripcion" required>
  <label>Día de la semana</label>
  <select name="dia_semana">
    <?php foreach ($dias as $d): ?>
      <option value="<?= e($d) ?>"><?= e($d) ?></option>
    <?php endforeach; ?>
  </select>
  <br><br>
  <label>Productos del menú</label><br>
  <?php foreach ($productos as $p): ?>
    <input type="checkbox" name="productos[]" value="<?= $p['id_producto'] ?>" id="p<?= $p['id_producto'] ?>">
    <label for="p<?= $p['id_producto'] ?>"><?= e($p['nombre']) ?></label><br>
  <?php endforeach; ?>
  <br><button class="btn" type="submit">Crear menú</button>
</form>

<table>
  <tr><th>ID</th><th>Descripción</th><th>Día</th><th>Nº productos</th><th>Acción</th></tr>
  <?php foreach ($menus as $m): ?>
  <tr>
    <td><?= e($m['id_menu']) ?></td>
    <td><?= e($m['descripcion']) ?></td>
    <td><?= e($m['dia_semana']) ?></td>
    <td><?= e($m['total']) ?></td>
    <td><a href="menus.php?eliminar=<?= $m['id_menu'] ?>" onclick="return confirm('¿Eliminar?')">Eliminar</a></td>
  </tr>
  <?php endforeach; ?>
</table>
</body></html>
