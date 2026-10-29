ALTER TABLE tbinventario MODIFY stock_actual DECIMAL(12,3) UNSIGNED NOT NULL DEFAULT 0, MODIFY stock_reservado DECIMAL(12,3) UNSIGNED NOT NULL DEFAULT 0, MODIFY stock_minimo DECIMAL(12,3) UNSIGNED NOT NULL DEFAULT 0;

CREATE TABLE tbstockmovimientos (
 id_movimiento BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 id_producto INT UNSIGNED NOT NULL,
 id_usuario INT UNSIGNED NULL,
 responsable VARCHAR(201) NOT NULL,
 tipo ENUM('inicial','entrada','salida','ajuste','minimo') NOT NULL,
 cantidad DECIMAL(12,3) NOT NULL DEFAULT 0,
 stock_antes DECIMAL(12,3) UNSIGNED NOT NULL,
 stock_despues DECIMAL(12,3) UNSIGNED NOT NULL,
 reservado DECIMAL(12,3) UNSIGNED NOT NULL,
 minimo_antes DECIMAL(12,3) UNSIGNED NOT NULL,
 minimo_despues DECIMAL(12,3) UNSIGNED NOT NULL,
 motivo VARCHAR(500) NOT NULL,
 solicitud CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL UNIQUE,
 huella CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_stockaudit_producto FOREIGN KEY (id_producto) REFERENCES tbproductos(id_producto),
 CONSTRAINT fk_stockaudit_usuario FOREIGN KEY (id_usuario) REFERENCES tbusuarios(id_usuario),
 CONSTRAINT chk_movimiento_reserva CHECK (reservado <= stock_despues),
 INDEX idx_movimiento_producto_fecha (id_producto,created_at,id_movimiento),
 INDEX idx_movimiento_fecha (created_at,id_movimiento),
 INDEX idx_movimiento_usuario (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tbstockmovimientos (id_producto,responsable,tipo,stock_antes,stock_despues,reservado,minimo_antes,minimo_despues,motivo)
 SELECT id_producto,'Sistema (saldo inicial)','inicial',stock_actual,stock_actual,stock_reservado,stock_minimo,stock_minimo,'Saldo conservado al habilitar el historial. No reconstruye movimientos anteriores.' FROM tbinventario;
