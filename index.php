<?php
require 'config.php';
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}
encabezado('Inicio');
?>
<p>Bienvenido al sistema de <strong>Comidas XYZ</strong>.</p>
<ul>
  <li><strong>Interfaz web</strong> (rol admin): administración de <a href="productos.php">productos</a> y <a href="menus.php">menús</a>.</li>
  <li><strong>Interfaz de escritorio</strong> (rol cajero, funciona en PC y móvil): <a href="facturacion.php">facturación a clientes</a>.</li>
</ul>
</body></html>
