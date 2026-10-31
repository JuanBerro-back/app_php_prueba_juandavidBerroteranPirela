-- =============================================================
--  Comidas XYZ - Base de datos "comidas_xyz"
--  Modelo relacional normalizado hasta la 3FN
--  Motor: MySQL / InnoDB - Juego de caracteres: utf8mb4
-- =============================================================

DROP DATABASE IF EXISTS comidas_xyz;
CREATE DATABASE comidas_xyz
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE comidas_xyz;

-- -------------------------------------------------------------
-- 1. Tabla: productos
-- -------------------------------------------------------------
CREATE TABLE productos (
    id_producto        INT            NOT NULL AUTO_INCREMENT,
    codigo_unico       VARCHAR(30)    NOT NULL,
    nombre             VARCHAR(100)   NOT NULL,
    descripcion        TEXT           NULL,
    tiempo_prep_min    INT            NULL,
    costo_antes_iva    DECIMAL(10,2)  NOT NULL,
    porcentaje_iva     DECIMAL(5,2)   NOT NULL DEFAULT 0.00,
    costo_despues_iva  DECIMAL(10,2)  NOT NULL,
    PRIMARY KEY (id_producto),
    UNIQUE KEY uq_codigo_unico (codigo_unico)
) ENGINE = InnoDB;

-- -------------------------------------------------------------
-- 2. Tabla: menus
-- -------------------------------------------------------------
CREATE TABLE menus (
    id_menu      INT          NOT NULL AUTO_INCREMENT,
    descripcion  VARCHAR(150) NOT NULL,
    dia_semana   ENUM('Lunes','Martes','Miercoles','Jueves',
                      'Viernes','Sabado','Domingo') NOT NULL,
    PRIMARY KEY (id_menu)
) ENGINE = InnoDB;

-- -------------------------------------------------------------
-- 3. Tabla puente: menu_productos  (resuelve N:M menus-productos)
-- -------------------------------------------------------------
CREATE TABLE menu_productos (
    id_menu      INT NOT NULL,
    id_producto  INT NOT NULL,
    PRIMARY KEY (id_menu, id_producto),
    CONSTRAINT fk_menuproductos_menu
        FOREIGN KEY (id_menu) REFERENCES menus (id_menu)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_menuproductos_producto
        FOREIGN KEY (id_producto) REFERENCES productos (id_producto)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB;

-- -------------------------------------------------------------
-- 4. Tabla: clientes
-- -------------------------------------------------------------
CREATE TABLE clientes (
    id_cliente   INT          NOT NULL AUTO_INCREMENT,
    tipo_id      ENUM('CC','TI','CE','NIT','Pasaporte') NOT NULL,
    numero_id    VARCHAR(20)  NOT NULL,
    nombre       VARCHAR(120) NOT NULL,
    celular      VARCHAR(20)  NULL,
    PRIMARY KEY (id_cliente),
    UNIQUE KEY uq_numero_id (numero_id)
) ENGINE = InnoDB;

-- -------------------------------------------------------------
-- 5. Tabla: facturas
-- -------------------------------------------------------------
CREATE TABLE facturas (
    id_factura      INT            NOT NULL AUTO_INCREMENT,
    id_cliente      INT            NOT NULL,
    fecha_emision   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    subtotal        DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
    impoconsumo_8   DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
    total_pagar     DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
    PRIMARY KEY (id_factura),
    CONSTRAINT fk_facturas_cliente
        FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB;

-- -------------------------------------------------------------
-- 6. Tabla puente: factura_detalles  (resuelve N:M facturas-productos)
-- -------------------------------------------------------------
CREATE TABLE factura_detalles (
    id_detalle      INT            NOT NULL AUTO_INCREMENT,
    id_factura      INT            NOT NULL,
    id_producto     INT            NOT NULL,
    cantidad        INT            NOT NULL DEFAULT 1,
    valor_unitario  DECIMAL(10,2)  NOT NULL,
    valor_total     DECIMAL(12,2)  NOT NULL,
    PRIMARY KEY (id_detalle),
    CONSTRAINT fk_detalle_factura
        FOREIGN KEY (id_factura) REFERENCES facturas (id_factura)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_detalle_producto
        FOREIGN KEY (id_producto) REFERENCES productos (id_producto)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB;

-- =============================================================
--  FIN DEL SCRIPT
-- =============================================================
