CREATE TABLE tbgoogleidentidades (
    google_subject VARCHAR(255) CHARACTER SET ascii COLLATE ascii_bin PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_google_usuario FOREIGN KEY (id_usuario) REFERENCES tbusuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
