-- Esquema inicial para instalaciones nuevas. No importar sobre una base existente.
CREATE TABLE tbroles (
    id_rol INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbusuarios (
    id_usuario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_rol INT UNSIGNED NOT NULL,
    nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    correo VARCHAR(254) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_rol) REFERENCES tbroles(id_rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbclientes (
    id_cliente INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL UNIQUE,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    telefono VARCHAR(30) NOT NULL,
    correo_contacto VARCHAR(254) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES tbusuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbcategorias (
    id_categoria INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(100) NOT NULL UNIQUE,
    estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbmarcas (
    id_marca INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_marca VARCHAR(100) NOT NULL UNIQUE,
    estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbproductos (
    id_producto INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT UNSIGNED NOT NULL,
    id_marca INT UNSIGNED NULL,
    codigo_producto VARCHAR(50) NOT NULL UNIQUE,
    nombre_producto VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    precio_compra DECIMAL(12,2) NOT NULL DEFAULT 0,
    precio_venta DECIMAL(12,2) NOT NULL,
    imagen VARCHAR(500) NULL,
    estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_productos_estado (estado),
    CONSTRAINT chk_productos_precios CHECK (precio_compra >= 0 AND precio_venta >= 0),
    FOREIGN KEY (id_categoria) REFERENCES tbcategorias(id_categoria),
    FOREIGN KEY (id_marca) REFERENCES tbmarcas(id_marca)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbinventario (
    id_inventario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_producto INT UNSIGNED NOT NULL UNIQUE,
    stock_actual INT UNSIGNED NOT NULL DEFAULT 0,
    stock_reservado INT UNSIGNED NOT NULL DEFAULT 0,
    stock_minimo INT UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT chk_inventario_reserva CHECK (stock_reservado <= stock_actual),
    FOREIGN KEY (id_producto) REFERENCES tbproductos(id_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbcarritos (
    id_carrito INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL UNIQUE,
    estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES tbusuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbdetallecarritos (
    id_detalle INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_carrito INT UNSIGNED NOT NULL,
    id_producto INT UNSIGNED NOT NULL,
    cantidad INT UNSIGNED NOT NULL,
    UNIQUE KEY uq_carrito_producto (id_carrito, id_producto),
    CONSTRAINT chk_carrito_cantidad CHECK (cantidad > 0),
    FOREIGN KEY (id_carrito) REFERENCES tbcarritos(id_carrito),
    FOREIGN KEY (id_producto) REFERENCES tbproductos(id_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tblistasdeseos (
    id_lista INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES tbusuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbdetallelistasdeseos (
    id_lista INT UNSIGNED NOT NULL,
    id_producto INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_lista, id_producto),
    FOREIGN KEY (id_lista) REFERENCES tblistasdeseos(id_lista),
    FOREIGN KEY (id_producto) REFERENCES tbproductos(id_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tablas necesarias para las metricas actuales. El flujo de pedidos y ventas
-- se ampliara mediante nuevas migraciones cuando se implemente.
CREATE TABLE tbpedidos (
    id_pedido INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL,
    estado ENUM('pendiente', 'confirmado', 'en_preparacion', 'listo', 'entregado', 'cancelado') NOT NULL DEFAULT 'pendiente',
    total DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pedidos_estado (estado),
    CONSTRAINT chk_pedidos_total CHECK (total >= 0),
    FOREIGN KEY (id_usuario) REFERENCES tbusuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbventas (
    id_venta INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT UNSIGNED NOT NULL UNIQUE,
    estado ENUM('pagada', 'anulada') NOT NULL DEFAULT 'pagada',
    total DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ventas_estado (estado),
    CONSTRAINT chk_ventas_total CHECK (total >= 0),
    FOREIGN KEY (id_pedido) REFERENCES tbpedidos(id_pedido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
