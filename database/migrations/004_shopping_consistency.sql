ALTER TABLE tbcarritos ADD COLUMN revision BIGINT UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE tbdetallecarritos ADD COLUMN precio_visto DECIMAL(12,2) NULL;
UPDATE tbdetallecarritos d JOIN tbproductos p ON p.id_producto=d.id_producto SET d.precio_visto=p.precio_venta;
CREATE TABLE tbshoppingoperaciones (
 solicitud CHAR(64) CHARACTER SET ascii COLLATE ascii_bin PRIMARY KEY,
 id_usuario INT UNSIGNED NOT NULL,
 huella CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
 avisos TEXT NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_shoppingoperation_usuario FOREIGN KEY (id_usuario) REFERENCES tbusuarios(id_usuario),
 INDEX idx_shoppingoperation_usuario (id_usuario,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
