-- ELECTRO MISIONES - Script de creación de BD (resumido para demo)
SET NAMES utf8mb4;
DROP DATABASE IF EXISTS electromisiones_db;
CREATE DATABASE electromisiones_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE electromisiones_db;
CREATE TABLE sucursales (id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(150), direccion VARCHAR(255), ciudad VARCHAR(100), provincia VARCHAR(100), telefono VARCHAR(50), email VARCHAR(150));
CREATE TABLE categorias (id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(100));
CREATE TABLE proveedores (id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(150));
CREATE TABLE productos (id INT AUTO_INCREMENT PRIMARY KEY, sku VARCHAR(50) UNIQUE, nombre VARCHAR(200), descripcion TEXT, categoria_id INT, proveedor_id INT, precio_compra DECIMAL(10,2), precio_venta DECIMAL(10,2), stock INT, activo TINYINT(1) DEFAULT 1, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL, FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE SET NULL) ENGINE=InnoDB;
INSERT INTO sucursales (nombre,direccion,ciudad,provincia,telefono,email) VALUES ('Electro Misiones - Posadas','Av. Junín 2550','Posadas','Misiones','0376-4429000','posadas@electromisiones.com.ar');
INSERT INTO categorias (nombre) VALUES ('Iluminación'),('Electrodomésticos'),('Telefonía');
INSERT INTO proveedores (nombre) VALUES ('Proveedor A SRL'),('Distribuidora Tech');
INSERT INTO productos (sku,nombre,categoria_id,proveedor_id,precio_compra,precio_venta,stock) VALUES ('EM-001','Lámpara LED 12W',1,1,200.00,350.00,50);
