INSERT INTO tbroles(nombre_rol) SELECT 'Empleado' WHERE NOT EXISTS (SELECT 1 FROM tbroles WHERE nombre_rol='Empleado');

ALTER TABLE tbpedidos
 ADD COLUMN codigo_pedido VARCHAR(30) NULL,
 ADD COLUMN id_cliente INT UNSIGNED NULL,
 ADD COLUMN retiro_solicitud CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL,
 ADD COLUMN retiro_huella CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL,
 ADD COLUMN contacto_nombre VARCHAR(201) NULL,
 ADD COLUMN contacto_telefono VARCHAR(30) NULL,
 ADD COLUMN contacto_correo VARCHAR(254) NULL,
 ADD COLUMN nota_retiro VARCHAR(500) NULL,
 ADD COLUMN fecha_vencimiento DATETIME NULL,
 ADD COLUMN reserva_activa TINYINT(1) NOT NULL DEFAULT 0,
 ADD COLUMN updated_at DATETIME NULL,
 ADD UNIQUE KEY uq_retiro_codigo (codigo_pedido),
 ADD UNIQUE KEY uq_retiro_solicitud (retiro_solicitud),
 ADD INDEX idx_retiro_owner (id_usuario,created_at),
 ADD INDEX idx_retiro_expira (reserva_activa,fecha_vencimiento);

CREATE TABLE tbretirodetalles (
 id_detalle BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 id_pedido INT UNSIGNED NOT NULL,
 id_producto INT UNSIGNED NOT NULL,
 codigo_producto VARCHAR(50) NOT NULL,
 nombre_producto VARCHAR(150) NOT NULL,
 cantidad INT UNSIGNED NOT NULL,
 precio_unitario DECIMAL(12,2) NOT NULL,
 subtotal DECIMAL(12,2) NOT NULL,
 UNIQUE KEY uq_retiro_producto (id_pedido,id_producto),
 CONSTRAINT fk_retirodetalle_pedido FOREIGN KEY (id_pedido) REFERENCES tbpedidos(id_pedido),
 CONSTRAINT fk_retirodetalle_producto FOREIGN KEY (id_producto) REFERENCES tbproductos(id_producto),
 CONSTRAINT chk_retiro_cantidad CHECK (cantidad>0),
 CONSTRAINT chk_retiro_precio CHECK (precio_unitario>=0 AND subtotal>=0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbretirohistorial (
 id_historial BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 id_pedido INT UNSIGNED NOT NULL,
 id_usuario INT UNSIGNED NULL,
 responsable VARCHAR(201) NOT NULL,
 estado_anterior VARCHAR(30) NULL,
 estado_nuevo VARCHAR(30) NOT NULL,
 motivo VARCHAR(500) NOT NULL,
 created_at DATETIME NOT NULL,
 CONSTRAINT fk_retirohistorial_pedido FOREIGN KEY (id_pedido) REFERENCES tbpedidos(id_pedido),
 CONSTRAINT fk_retirohistorial_usuario FOREIGN KEY (id_usuario) REFERENCES tbusuarios(id_usuario),
 INDEX idx_retiro_historial (id_pedido,id_historial)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE tbstockmovimientos
 MODIFY tipo ENUM('inicial','entrada','salida','ajuste','minimo','reserva','liberacion','entrega') NOT NULL,
 ADD COLUMN reservado_antes DECIMAL(12,3) UNSIGNED NULL,
 ADD COLUMN id_pedido INT UNSIGNED NULL,
 ADD CONSTRAINT fk_stock_retiro FOREIGN KEY (id_pedido) REFERENCES tbpedidos(id_pedido);

CREATE TABLE tbretiocorreos (
 id_correo BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 id_pedido INT UNSIGNED NOT NULL UNIQUE,
 estado ENUM('pendiente','procesando','enviado','omitido') NOT NULL DEFAULT 'pendiente',
 intentos INT UNSIGNED NOT NULL DEFAULT 0,
 bloqueo CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL,
 proximo_intento DATETIME NOT NULL,
 enviado_at DATETIME NULL,
 created_at DATETIME NOT NULL,
 CONSTRAINT fk_retiocorreo_pedido FOREIGN KEY (id_pedido) REFERENCES tbpedidos(id_pedido),
 INDEX idx_retiocorreo_pendiente (estado,proximo_intento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
