<?php
/**
 * Comidas XYZ - Configuración y utilidades
 */
session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'comidas_xyz');
define('DB_USER', 'root');
define('DB_PASS', '');

define('IMPUESTO_CONSUMO', 0.08); // 8% impuesto al consumo

function conectar() {
    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        return $pdo;
    } catch (PDOException $e) {
        die('Error de conexión: ' . htmlspecialchars($e->getMessage()));
    }
}

/** Escapa texto para HTML */
function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/** Obliga a tener un rol (admin / cajero) para acceder */
function requiere_rol($rol) {
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== $rol) {
        header('Location: login.php');
        exit;
    }
}

/** Encabezado HTML común */
function encabezado($titulo) {
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . e($titulo) . ' - Comidas XYZ</title>';
    echo '<style>
        body{font-family:Segoe UI,Arial,sans-serif;margin:20px;color:#222}
        nav{background:#2c6e49;padding:10px;border-radius:6px;margin-bottom:20px}
        nav a{color:#fff;text-decoration:none;margin-right:15px;font-weight:bold}
        nav span{color:#dcefe3;float:right}
        table{border-collapse:collapse;width:100%;margin-top:10px}
        th,td{border:1px solid #999;padding:6px 10px;text-align:left}
        th{background:#e8f0ea}
        input,select{margin:3px;padding:5px}
        label{font-weight:bold}
        .btn{background:#2c6e49;color:#fff;border:0;padding:8px 16px;border-radius:4px;cursor:pointer}
        .ok{color:#2c6e49;font-weight:bold}
        .error{color:#c0392b;font-weight:bold}
        .factura{border:2px solid #2c6e49;border-radius:8px;padding:16px;margin-top:20px;max-width:600px}
        .factura h2{color:#2c6e49;margin-top:0}
    </style></head><body>';
    echo '<nav><a href="index.php">Inicio</a>';
    echo '<a href="productos.php">Productos</a>';
    echo '<a href="menus.php">Menús</a>';
    echo '<a href="facturacion.php">Facturación</a>';
    if (isset($_SESSION['usuario'])) {
        echo '<span>' . e($_SESSION['usuario']) . ' (' . e($_SESSION['rol']) . ') <a href="logout.php">Salir</a></span>';
    }
    echo '</nav>';
    echo '<h1>' . e($titulo) . '</h1>';
}
