CREATE TABLE tbserviciocategorias (
 id_categoria INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(100) NOT NULL UNIQUE,
 estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbserviciocatalogo (
 id_servicio INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 id_categoria INT UNSIGNED NOT NULL,
 nombre VARCHAR(120) NOT NULL,
 descripcion VARCHAR(2000) NOT NULL,
 unidad VARCHAR(60) NOT NULL,
 precio_base DECIMAL(12,2) NULL,
 tiempo VARCHAR(150) NOT NULL DEFAULT 'A confirmar al cotizar',
 material_fisico TINYINT(1) NOT NULL DEFAULT 0,
 estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
 revision INT UNSIGNED NOT NULL DEFAULT 0,
 CONSTRAINT fk_servicio_categoria FOREIGN KEY(id_categoria) REFERENCES tbserviciocategorias(id_categoria),
 CONSTRAINT ck_servicio_precio CHECK(precio_base IS NULL OR precio_base>=0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbserviciosolicitudes (
 id_solicitud INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 codigo VARCHAR(30) NOT NULL UNIQUE,
 id_servicio INT UNSIGNED NOT NULL,
 id_usuario INT UNSIGNED NOT NULL,
 nombre_servicio VARCHAR(120) NOT NULL,
 unidad VARCHAR(60) NOT NULL,
 cantidad INT UNSIGNED NOT NULL,
 descripcion VARCHAR(4000) NOT NULL,
 opciones TEXT NOT NULL,
 contacto_nombre VARCHAR(201) NOT NULL,
 contacto_correo VARCHAR(150) NOT NULL,
 contacto_telefono VARCHAR(20) NOT NULL,
 fecha_preferida DATE NULL,
 material_fisico TINYINT(1) NOT NULL DEFAULT 0,
 material_recibido TINYINT(1) NOT NULL DEFAULT 0,
 estado ENUM('nueva','en_cotizacion','cotizada','aceptada','en_proceso','lista','entregada','cancelada') NOT NULL DEFAULT 'nueva',
 revision INT UNSIGNED NOT NULL DEFAULT 0,
 total DECIMAL(12,2) NULL,
 fecha_entrega DATETIME NULL,
 operacion CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL UNIQUE,
 huella CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL,
 CONSTRAINT fk_serviciosolicitud_servicio FOREIGN KEY(id_servicio) REFERENCES tbserviciocatalogo(id_servicio),
 CONSTRAINT fk_serviciosolicitud_usuario FOREIGN KEY(id_usuario) REFERENCES tbusuarios(id_usuario),
 CONSTRAINT ck_serviciosolicitud_cantidad CHECK(cantidad BETWEEN 1 AND 10000),
 CONSTRAINT ck_serviciosolicitud_total CHECK(total IS NULL OR total>=0),
 INDEX idx_serviciosolicitud_usuario(id_usuario,id_solicitud),
 INDEX idx_serviciosolicitud_estado(estado,id_solicitud)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbserviciocotizaciones (
 id_cotizacion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 id_solicitud INT UNSIGNED NOT NULL,
 version INT UNSIGNED NOT NULL,
 conceptos TEXT NOT NULL,
 total DECIMAL(12,2) NOT NULL,
 condiciones VARCHAR(2000) NOT NULL,
 fecha_entrega DATETIME NOT NULL,
 vence_at DATETIME NOT NULL,
 decision ENUM('pendiente','aceptada','rechazada','sustituida') NOT NULL DEFAULT 'pendiente',
 id_actor INT UNSIGNED NOT NULL,
 decidida_at DATETIME NULL,
 created_at DATETIME NOT NULL,
 UNIQUE KEY uq_serviciocotizacion_version(id_solicitud,version),
 CONSTRAINT fk_serviciocotizacion_solicitud FOREIGN KEY(id_solicitud) REFERENCES tbserviciosolicitudes(id_solicitud),
 CONSTRAINT fk_serviciocotizacion_actor FOREIGN KEY(id_actor) REFERENCES tbusuarios(id_usuario),
 CONSTRAINT ck_serviciocotizacion_total CHECK(total>=0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbservicioarchivos (
 id_archivo BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 id_solicitud INT UNSIGNED NOT NULL,
 id_actor INT UNSIGNED NOT NULL,
 nombre VARCHAR(200) NOT NULL,
 ruta VARCHAR(80) CHARACTER SET ascii COLLATE ascii_bin NOT NULL UNIQUE,
 mime VARCHAR(100) NOT NULL,
 bytes INT UNSIGNED NOT NULL,
 sha256 CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
 tipo ENUM('original','resultado') NOT NULL,
 eliminado_at DATETIME NULL,
 created_at DATETIME NOT NULL,
 CONSTRAINT fk_servicioarchivo_solicitud FOREIGN KEY(id_solicitud) REFERENCES tbserviciosolicitudes(id_solicitud),
 CONSTRAINT fk_servicioarchivo_actor FOREIGN KEY(id_actor) REFERENCES tbusuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbserviciohistorial (
 id_evento BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 id_solicitud INT UNSIGNED NOT NULL,
 id_actor INT UNSIGNED NOT NULL,
 accion VARCHAR(40) NOT NULL,
 detalle VARCHAR(2000) NOT NULL,
 operacion CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL UNIQUE,
 huella CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL,
 created_at DATETIME NOT NULL,
 CONSTRAINT fk_serviciohistorial_solicitud FOREIGN KEY(id_solicitud) REFERENCES tbserviciosolicitudes(id_solicitud),
 CONSTRAINT fk_serviciohistorial_actor FOREIGN KEY(id_actor) REFERENCES tbusuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tbserviciocorreos (
 id_correo BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 id_solicitud INT UNSIGNED NOT NULL,
 revision INT UNSIGNED NOT NULL,
 tipo VARCHAR(40) NOT NULL,
 estado ENUM('pendiente','procesando','enviado','omitido') NOT NULL DEFAULT 'pendiente',
 intentos INT UNSIGNED NOT NULL DEFAULT 0,
 bloqueo CHAR(64) NULL,
 proximo_intento DATETIME NOT NULL,
 created_at DATETIME NOT NULL,
 UNIQUE KEY uq_serviciocorreo_evento(id_solicitud,revision,tipo),
 CONSTRAINT fk_serviciocorreo_solicitud FOREIGN KEY(id_solicitud) REFERENCES tbserviciosolicitudes(id_solicitud)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE tbpersonalpermisos ADD COLUMN servicios TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE tbventas MODIFY id_pedido INT UNSIGNED NULL,
 ADD COLUMN id_solicitud_servicio INT UNSIGNED NULL UNIQUE,
 ADD CONSTRAINT fk_venta_servicio FOREIGN KEY(id_solicitud_servicio) REFERENCES tbserviciosolicitudes(id_solicitud),
 ADD CONSTRAINT ck_venta_origen CHECK((id_pedido IS NOT NULL AND id_solicitud_servicio IS NULL) OR (id_pedido IS NULL AND id_solicitud_servicio IS NOT NULL));

INSERT INTO tbserviciocategorias(nombre) VALUES ('Documentos'),('Fotografías y stickers'),('Acabados');
INSERT INTO tbserviciocatalogo(id_categoria,nombre,descripcion,unidad,material_fisico) VALUES
 (1,'Copias','Copias de tus documentos. Lleva los originales al local.','página',1),
 (1,'Impresiones','Impresión de documentos en blanco y negro o color.','página',0),
 (2,'Fotografías','Impresión de fotografías según las medidas solicitadas.','fotografía',0),
 (2,'Stickers','Impresión de stickers. Indica medidas, cantidad y preparación necesaria.','trabajo',0),
 (1,'Escaneos','Digitalización de documentos originales entregados en tienda.','página',1),
 (1,'Digitación','Preparación de documentos a partir de tus instrucciones y referencias.','trabajo',0),
 (3,'Anillado','Encuadernación de documentos entregados en tienda.','documento',1),
 (3,'Plastificado','Protección de documentos originales entregados en tienda.','documento',1);
