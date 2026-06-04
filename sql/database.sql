-- ============================================================
-- Sistema de Gestión de Inventario y Proveedores - Restaurante
-- ============================================================

CREATE DATABASE IF NOT EXISTS restaurante_inventario CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE restaurante_inventario;

-- Tabla de usuarios (Adaptada para el login_sencillo)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usunombre VARCHAR(100) NOT NULL UNIQUE,
    usuclave VARCHAR(255) NOT NULL,
    correo VARCHAR(150),
    pregunta_seguridad VARCHAR(100),
    respuesta_seguridad VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de categorías
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de insumos
CREATE TABLE IF NOT EXISTS insumos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    stock_actual DECIMAL(10,2) NOT NULL DEFAULT 0,
    stock_minimo DECIMAL(10,2) NOT NULL DEFAULT 0,
    precio_unitario DECIMAL(10,2) NOT NULL DEFAULT 0,
    fecha_vencimiento DATE NULL,
    categoria_id INT NOT NULL,
    imagen_ruta VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE RESTRICT
);

-- ============================================================
-- DATOS DE PRUEBA
-- ============================================================

-- Usuarios de prueba (Las contraseñas están encriptadas con md5 como requiere login_sencillo)
-- admin: password | chef: chef2024
INSERT INTO usuarios (usunombre, usuclave, correo, pregunta_seguridad, respuesta_seguridad) VALUES
('admin', '5f4dcc3b5aa765d61d8327deb882cf99', 'admin@restaurante.com', 'mascota', 'firulais'),
('chef', '34f0d771b9330da8933b9347d4835252', 'chef@restaurante.com', 'ciudad', 'bogota');

-- Categorías
INSERT INTO categorias (nombre) VALUES
('Carnes y Aves'),
('Verduras y Hortalizas'),
('Lácteos y Huevos'),
('Cereales y Granos'),
('Aceites y Condimentos'),
('Bebidas'),
('Mariscos y Pescados'),
('Frutas'),
('Embutidos'),
('Congelados');

-- Insumos de prueba
INSERT INTO insumos (nombre, stock_actual, stock_minimo, precio_unitario, fecha_vencimiento, categoria_id, imagen_ruta) VALUES
('Pollo Entero (kg)',   45.00, 20.00,  8500.00,  '2025-07-15', 1, NULL),
('Res Molida (kg)',      8.00, 15.00, 12000.00,  '2025-07-10', 1, NULL),
('Costillas de Cerdo',  22.00, 10.00, 15000.00,  '2025-07-12', 1, NULL),
('Lechuga Romana',       3.00, 10.00,   800.00,  '2025-07-05', 2, NULL),
('Tomate Chonto (kg)',  18.00,  8.00,  1200.00,  '2025-07-08', 2, NULL),
('Papa Pastusa (kg)',   60.00, 25.00,   900.00,  '2025-08-01', 2, NULL),
('Zanahoria (kg)',       5.00, 12.00,   700.00,  '2025-07-20', 2, NULL),
('Cebolla Cabezona',    14.00,  8.00,   950.00,  '2025-07-25', 2, NULL),
('Leche (litros)',       2.00, 20.00,  1800.00,  '2025-07-06', 3, NULL),
('Queso Campesino (kg)',10.00,  5.00, 14000.00,  '2025-07-15', 3, NULL),
('Huevos (30 ud)',      15.00,  5.00,  9500.00,  '2025-07-30', 3, NULL),
('Crema de Leche',       1.00,  8.00,  4500.00,  '2025-07-12', 3, NULL),
('Arroz Diana (kg)',    80.00, 30.00,  2800.00,  NULL,         4, NULL),
('Harina de Trigo (kg)',35.00, 15.00,  1900.00,  '2025-09-01', 4, NULL),
('Lentejas (kg)',       20.00, 10.00,  3200.00,  '2026-01-01', 4, NULL),
('Aceite Vegetal (lt)', 12.00,  6.00,  7500.00,  '2026-03-01', 5, NULL),
('Sal (kg)',            25.00,  5.00,   600.00,  NULL,         5, NULL),
('Ajo en Polvo',         0.00,  3.00,  4200.00,  '2025-12-01', 5, NULL),
('Pimienta Negra',       2.00,  3.00,  5800.00,  '2025-11-01', 5, NULL),
('Gaseosa (caja 24ud)', 6.00,  4.00, 38000.00,  '2025-10-01', 6, NULL),
('Agua Mineral (caja)', 3.00,  5.00, 22000.00,  '2026-01-01', 6, NULL),
('Salmón Filete (kg)',   4.00,  3.00, 45000.00,  '2025-07-07', 7, NULL),
('Camarón Tigre (kg)',   0.00,  2.00, 38000.00,  '2025-07-08', 7, NULL),
('Banano (racimo)',      8.00,  4.00,  3500.00,  '2025-07-08', 8, NULL),
('Mango Tommy (kg)',    12.00,  5.00,  2800.00,  '2025-07-10', 8, NULL),
('Salchicha Frankfurt', 6.00,  4.00,  8900.00,  '2025-07-20', 9, NULL),
('Jamón Cocido (kg)',   3.00,  5.00, 18000.00,  '2025-07-15', 9, NULL),
('Pizza Congelada',      8.00,  6.00, 22000.00,  '2025-12-01', 10, NULL);
