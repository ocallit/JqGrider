CREATE TABLE IF NOT EXISTS categoria (
    categoria VARCHAR(191) NOT NULL PRIMARY KEY,
    label_singular VARCHAR(191) NOT NULL,
    label_plural VARCHAR(191) NOT NULL,
    min_select SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    max_select SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    activo ENUM ('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
    orden     SMALLINT UNSIGNED NOT NULL DEFAULT 100,
    UNIQUE KEY (label_singular),
    UNIQUE KEY (label_plural)
);

CREATE TABLE IF NOT EXISTS catego (
    catego_id MEDIUMINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categoria    VARCHAR(191) NOT NULL,
    CONSTRAINT fk_catego_categoria FOREIGN KEY (categoria) REFERENCES categoria (categoria) ON DELETE RESTRICT ON UPDATE CASCADE,
    label     VARCHAR(191) NOT NULL,
    activo    ENUM ('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
    orden     SMALLINT UNSIGNED NOT NULL DEFAULT 100,
    UNIQUE KEY (categoria, label),
    KEY (categoria, orden, label)
);

CREATE TABLE lookup_registry (
    id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(191) NOT NULL,
    plural VARCHAR(191) NOT NULL DEFAULT '',
    activo ENUM ('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
    orden SMALLINT UNSIGNED NOT NULL DEFAULT 100,
    registrado DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    registrado_por VARCHAR(16) NOT NULL DEFAULT '?',
    ultimo_cambio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ultimo_cambio_por VARCHAR(16) NOT NULL DEFAULT '?',
    UNIQUE KEY unico(label)
);

CREATE TABLE lookup (
    id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(191) NOT NULL,
    activo ENUM ('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
    orden SMALLINT UNSIGNED NOT NULL DEFAULT 100,
    registrado DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    registrado_por VARCHAR(16) NOT NULL DEFAULT '?',
    ultimo_cambio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ultimo_cambio_por VARCHAR(16) NOT NULL DEFAULT '?',
    UNIQUE KEY nombre_unico(label)
);



