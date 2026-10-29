ALTER TABLE tbusuarios ADD COLUMN session_version INT UNSIGNED NOT NULL DEFAULT 0, ADD COLUMN cuenta_revision INT UNSIGNED NOT NULL DEFAULT 0;

CREATE TABLE tbpersonalpermisos (
 id_usuario INT UNSIGNED PRIMARY KEY,
 pedidos TINYINT(1) NOT NULL DEFAULT 0,
 id_actor INT UNSIGNED NOT NULL,
 updated_at DATETIME NOT NULL,
 CONSTRAINT fk_personalpermiso_usuario FOREIGN KEY(id_usuario) REFERENCES tbusuarios(id_usuario),
 CONSTRAINT fk_personalpermiso_actor FOREIGN KEY(id_actor) REFERENCES tbusuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE tbstockmovimientos MODIFY tipo ENUM('inicial','entrada','salida','ajuste','minimo','reserva','liberacion','entrega','devolucion') NOT NULL;

ALTER TABLE tbventas
 ADD COLUMN codigo_venta VARCHAR(30) NULL,
 ADD COLUMN id_usuario INT UNSIGNED NULL,
 ADD COLUMN id_cliente INT UNSIGNED NULL,
 ADD COLUMN fecha_pago DATETIME NULL,
 ADD COLUMN metodo_pago VARCHAR(30) NULL,
 ADD COLUMN recibido DECIMAL(12,2) NULL,
 ADD COLUMN cambio DECIMAL(12,2) NULL,
 ADD COLUMN responsable_cobro VARCHAR(201) NULL,
 ADD COLUMN cobro_solicitud CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL,
 ADD COLUMN cobro_huella CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL,
 ADD COLUMN pedido_cobrado INT UNSIGNED GENERATED ALWAYS AS (CASE WHEN cobro_solicitud IS NOT NULL THEN id_pedido ELSE NULL END) STORED,
 ADD UNIQUE KEY uq_cobro_solicitud(cobro_solicitud),
 ADD UNIQUE KEY uq_cobro_pedido(pedido_cobrado),
 ADD INDEX idx_cobro_fecha(fecha_pago,estado);

CREATE TABLE tbventasanulaciones (
 id_anulacion BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 id_venta INT UNSIGNED NOT NULL UNIQUE,
 id_usuario INT UNSIGNED NOT NULL,
 responsable VARCHAR(201) NOT NULL,
 reembolso DECIMAL(12,2) NOT NULL,
 stock_devuelto TINYINT(1) NOT NULL,
 motivo VARCHAR(500) NOT NULL,
 solicitud CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL UNIQUE,
 huella CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
 created_at DATETIME NOT NULL,
 CONSTRAINT fk_anulacion_venta FOREIGN KEY(id_venta) REFERENCES tbventas(id_venta),
 CONSTRAINT fk_anulacion_usuario FOREIGN KEY(id_usuario) REFERENCES tbusuarios(id_usuario),
 CONSTRAINT chk_anulacion_reembolso CHECK(reembolso>=0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbcuentashistorial (
 id_evento BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 id_usuario INT UNSIGNED NOT NULL,
 id_actor INT UNSIGNED NULL,
 accion VARCHAR(40) NOT NULL,
 detalle VARCHAR(500) NOT NULL,
 created_at DATETIME NOT NULL,
 CONSTRAINT fk_cuentahistorial_usuario FOREIGN KEY(id_usuario) REFERENCES tbusuarios(id_usuario),
 CONSTRAINT fk_cuentahistorial_actor FOREIGN KEY(id_actor) REFERENCES tbusuarios(id_usuario),
 INDEX idx_cuentahistorial_usuario(id_usuario,id_evento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbclaverecuperacion (
 id_recuperacion BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 id_usuario INT UNSIGNED NOT NULL,
 token_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL UNIQUE,
 session_version INT UNSIGNED NOT NULL,
 expires_at DATETIME NOT NULL,
 used_at DATETIME NULL,
 created_at DATETIME NOT NULL,
 CONSTRAINT fk_claverecuperacion_usuario FOREIGN KEY(id_usuario) REFERENCES tbusuarios(id_usuario),
 INDEX idx_recuperacion_usuario(id_usuario,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
