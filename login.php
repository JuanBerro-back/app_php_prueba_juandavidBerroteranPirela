<?php
/**
 * Comidas XYZ - Login con roles (admin / cajero)
 *
 * Requiere la tabla usuarios (ver usuarios.sql):
 *   CREATE TABLE usuarios (
 *     id_usuario INT AUTO_INCREMENT PRIMARY KEY,
 *     usuario    VARCHAR(50)  NOT NULL UNIQUE,
 *     clave      VARCHAR(64)  NOT NULL,   -- SHA2-256
 *     rol        ENUM('admin','cajero') NOT NULL
 *   );
 */
require 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $clave   = $_POST['clave'] ?? '';

    $pdo = conectar();
    $stmt = $pdo->prepare('SELECT rol FROM usuarios WHERE usuario = ? AND clave = SHA2(?, 256)');
    $stmt->execute([$usuario, $clave]);
    $r = $stmt->fetch();

    if ($r) {
        $_SESSION['usuario'] = $usuario;
        $_SESSION['rol'] = $r['rol'];
        header('Location: index.php');
        exit;
    }
    $error = 'Usuario o contraseña incorrectos.';
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - Comidas XYZ</title>
<style>
  body{font-family:Segoe UI,Arial,sans-serif;background:#2c6e49;display:flex;justify-content:center;align-items:center;height:100vh;margin:0}
  .caja{background:#fff;padding:30px;border-radius:8px;width:300px;box-shadow:0 4px 12px rgba(0,0,0,.3)}
  h2{margin-top:0;color:#2c6e49}
  input{width:100%;padding:8px;margin:6px 0;box-sizing:border-box}
  .btn{width:100%;background:#2c6e49;color:#fff;border:0;padding:10px;border-radius:4px;font-weight:bold;cursor:pointer}
  .error{color:#c0392b;font-weight:bold}
</style>
</head>
<body>
<form class="caja" method="post">
  <h2>Comidas XYZ</h2>
  <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
  <label>Usuario</label>
  <input type="text" name="usuario" required>
  <label>Contraseña</label>
  <input type="password" name="clave" required>
  <button class="btn" type="submit">Ingresar</button>
</form>
</body>
</html>
