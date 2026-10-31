<?php
/**
 * Comidas XYZ - Facturación a clientes (interfaz de escritorio, rol cajero)
 * RF-08 a RF-14: registrar cliente, agregar platos, calcular subtotal,
 * impuesto al consumo (8%) y total; genera la factura.
 * Funciona en PC y dispositivos móviles (interfaz responsiva).
 */
require 'config.php';
requiere_rol('cajero');
$pdo = conectar();

$productos = $pdo->query('SELECT * FROM productos ORDER BY nombre')->fetchAll(PDO::FETCH_ASSOC);

$factura  = null;
$detalles = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_id   = $_POST['tipo_id'];
    $numero_id = trim($_POST['numero_id']);
    $nombre    = trim($_POST['nombre']);
    $celular   = trim($_POST['celular']);
    $cantidades = $_POST['cantidad'] ?? []; // producto_id => cantidad

    // 1) Cliente: buscar por número de identificación o crear
    $stmt = $pdo->prepare('SELECT id_cliente FROM clientes WHERE numero_id = ?');
    $stmt->execute([$numero_id]);
    $cliente = $stmt->fetch();
    if ($cliente) {
        $id_cliente = $cliente['id_cliente'];
        $pdo->prepare('UPDATE clientes SET nombre=?, celular=?, tipo_id=? WHERE id_cliente=?')
            ->execute([$nombre, $celular, $tipo_id, $id_cliente]);
    } else {
        $pdo->prepare('INSERT INTO clientes (tipo_id, numero_id, nombre, celular) VALUES (?,?,?,?)')
            ->execute([$tipo_id, $numero_id, $nombre, $celular]);
        $id_cliente = $pdo->lastInsertId();
    }

    // 2) Calcular subtotal y armar líneas
    $subtotal = 0.0;
    $lineas = [];
    foreach ($cantidades as $pid => $cant) {
        $cant = (int)$cant;
        if ($cant <= 0) continue;
        $stmt = $pdo->prepare('SELECT nombre, costo_despues_iva FROM productos WHERE id_producto = ?');
        $stmt->execute([(int)$pid]);
        $prod = $stmt->fetch();
        if (!$prod) continue;
        $unit  = (float)$prod['costo_despues_iva'];
        $total = $unit * $cant;
        $subtotal += $total;
        $lineas[] = ['nombre'=>$prod['nombre'], 'cantidad'=>$cant,
                     'valor_unitario'=>$unit, 'valor_total'=>$total];
    }

    // 3) Impuesto al consumo (8%) y total
    $impuesto    = $subtotal * IMPUESTO_CONSUMO;
    $total_pagar = $subtotal + $impuesto;

    // 4) Insertar factura
    $pdo->prepare('INSERT INTO facturas (id_cliente, subtotal, impoconsumo_8, total_pagar)
                   VALUES (?,?,?,?)')
        ->execute([$id_cliente, $subtotal, $impuesto, $total_pagar]);
    $id_factura = $pdo->lastInsertId();

    // 5) Insertar detalle
    $ins = $pdo->prepare('INSERT INTO factura_detalles
                          (id_factura, id_producto, cantidad, valor_unitario, valor_total)
                          VALUES (?,?,?,?,?)');
    foreach ($cantidades as $pid => $cant) {
        $cant = (int)$cant;
        if ($cant <= 0) continue;
        $stmt = $pdo->prepare('SELECT costo_despues_iva FROM productos WHERE id_producto = ?');
        $stmt->execute([(int)$pid]);
        $prod = $stmt->fetch();
        if (!$prod) continue;
        $unit = (float)$prod['costo_despues_iva'];
        $ins->execute([$id_factura, (int)$pid, $cant, $unit, $unit * $cant]);
    }

    // 6) Datos para mostrar la factura
    $factura = ['id_factura'=>$id_factura, 'numero_id'=>$numero_id, 'nombre'=>$nombre,
                'tipo_id'=>$tipo_id, 'subtotal'=>$subtotal, 'impuesto'=>$impuesto,
                'total'=>$total_pagar, 'fecha'=>date('Y-m-d H:i:s')];
    $detalles = $lineas;
}

encabezado('Facturación');
?>

<form method="post">
  <h2>Datos del cliente</h2>
  <label>Tipo ID</label>
  <select name="tipo_id">
    <?php foreach (['CC','TI','CE','NIT','Pasaporte'] as $t): ?>
      <option><?= $t ?></option>
    <?php endforeach; ?>
  </select>
  <label>Nº identificación</label><input type="text" name="numero_id" required>
  <label>Nombre</label><input type="text" name="nombre" required>
  <label>Celular</label><input type="text" name="celular">

  <h2>Platos consumidos</h2>
  <table>
    <tr><th>Producto</th><th>Precio (después IVA)</th><th>Cantidad</th></tr>
    <?php foreach ($productos as $p): ?>
    <tr>
      <td><?= e($p['nombre']) ?></td>
      <td><?= number_format($p['costo_despues_iva'], 2) ?></td>
      <td><input type="number" name="cantidad[<?= $p['id_producto'] ?>]" min="0" value="0" style="width:70px"></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <br><button class="btn" type="submit">Generar factura</button>
</form>

<?php if ($factura): ?>
<div class="factura">
  <h2>Factura Nº <?= e($factura['id_factura']) ?></h2>
  <p><strong>Fecha:</strong> <?= e($factura['fecha']) ?><br>
     <strong>Cliente:</strong> <?= e($factura['nombre']) ?> (<?= e($factura['tipo_id']) ?> <?= e($factura['numero_id']) ?>)</p>
  <table>
    <tr><th>Producto</th><th>Valor unitario</th><th>Cantidad</th><th>Valor total</th></tr>
    <?php foreach ($detalles as $l): ?>
    <tr>
      <td><?= e($l['nombre']) ?></td>
      <td><?= number_format($l['valor_unitario'], 2) ?></td>
      <td><?= e($l['cantidad']) ?></td>
      <td><?= number_format($l['valor_total'], 2) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <p><strong>Subtotal:</strong> <?= number_format($factura['subtotal'], 2) ?><br>
     <strong>Impuesto al consumo (8%):</strong> <?= number_format($factura['impuesto'], 2) ?><br>
     <strong>Total a pagar:</strong> <?= number_format($factura['total'], 2) ?></p>
</div>
<?php endif; ?>
</body></html>
