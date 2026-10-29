ALTER TABLE tbretiocorreos
 ADD COLUMN tipo ENUM('confirmado','listo') NOT NULL DEFAULT 'confirmado' AFTER id_pedido,
 ADD INDEX idx_retiocorreo_pedido (id_pedido),
 DROP INDEX id_pedido,
 ADD UNIQUE KEY uq_retiocorreo_evento (id_pedido,tipo);
