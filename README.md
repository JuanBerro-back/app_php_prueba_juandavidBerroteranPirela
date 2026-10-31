# Comidas XYZ — Sistema de Gestión (PHP + MySQL)

Aplicación para el restaurante **Comidas XYZ** que sistematiza dos procesos:

- **Administración de productos y menús** (interfaz **web**).
- **Facturación a clientes** (interfaz de **escritorio**, responsive: PC y móvil).

Ambas interfaces comparten una única base de datos (`comidas_xyz`), modelada en **Tercera Forma Normal (3FN)**.

## Estructura del repositorio

| Archivo | Descripción |
|---|---|
| `comidas_xyz.sql` | Script de creación de la base de datos y sus 6 tablas (3FN) |
| `usuarios.sql` | Tabla `usuarios` (roles admin/cajero) y usuarios de prueba |
| `config.php` | Conexión PDO, sesión, roles y utilidades |
| `login.php` / `logout.php` | Inicio y cierre de sesión con roles |
| `index.php` | Menú principal |
| `productos.php` | Interfaz WEB: CRUD de productos + cálculo de IVA |
| `menus.php` | Interfaz WEB: menús por día con varios productos (N:M) |
| `facturacion.php` | Interfaz ESCRITORIO: cliente, platos, subtotal, impuesto 8% y total |

## Modelo de datos

- `productos` (PK id_producto, UQ codigo_unico)
- `menus` (PK id_menu)
- `menu_productos` (tabla puente, resuelve N:M productos-menús)
- `clientes` (PK id_cliente, UQ numero_id)
- `facturas` (PK id_factura, FK id_cliente)
- `factura_detalles` (tabla puente, resuelve N:M facturas-productos)

## Requisitos

- PHP 7.4 o superior (XAMPP / WAMP / Laragon).
- MySQL / MariaDB.

## Instalación

1. Importa `comidas_xyz.sql` en MySQL.
2. Importa `usuarios.sql`.
3. Copia los archivos `.php` a `htdocs` (ej. `C:\xampp\htdocs\comidas_xyz_app`).
4. Abre `http://localhost/comidas_xyz_app/login.php`.

## Usuarios de prueba

| Usuario | Contraseña | Rol |
|---|---|---|
| `admin` | `admin123` | Administrador (productos y menús) |
| `cajero` | `cajero123` | Cajero (facturación) |

## Reglas de cálculo de la factura

- `valor_total = valor_unitario × cantidad`
- `subtotal = Σ valor_total`
- `impuesto al consumo = subtotal × 8%`
- `total a pagar = subtotal + impuesto`
