CREATE TABLE IF NOT EXISTS tbreinversiones (
 id_reinversion INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 id_venta INT UNSIGNED NOT NULL,
 monto_venta DECIMAL(12,2) NOT NULL,
 porcentaje_reposicion DECIMAL(5,2) NOT NULL DEFAULT 50.00,
 porcentaje_crecimiento DECIMAL(5,2) NOT NULL DEFAULT 5.00,
 monto_reposicion DECIMAL(12,2) GENERATED ALWAYS AS (ROUND(monto_venta * porcentaje_reposicion / 100, 2)) STORED,
 monto_crecimiento DECIMAL(12,2) GENERATED ALWAYS AS (ROUND(monto_venta * (porcentaje_reposicion + porcentaje_crecimiento) / 100, 2) - ROUND(monto_venta * porcentaje_reposicion / 100, 2)) STORED,
 total_reinversion DECIMAL(12,2) GENERATED ALWAYS AS (ROUND(monto_venta * (porcentaje_reposicion + porcentaje_crecimiento) / 100, 2)) STORED,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_reinversion_venta (id_venta),
 CONSTRAINT fk_reinversion_venta FOREIGN KEY (id_venta) REFERENCES tbventas(id_venta),
 CONSTRAINT ck_reinversion_base CHECK (monto_venta >= 0),
 CONSTRAINT ck_reinversion_porcentajes CHECK (porcentaje_reposicion >= 0 AND porcentaje_crecimiento >= 0 AND porcentaje_reposicion + porcentaje_crecimiento <= 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
