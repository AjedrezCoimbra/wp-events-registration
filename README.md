# Plugin de Gestión de Torneos de Ajedrez

Plugin de WordPress del Club de Ajedrez Coimbra de Jumilla para la gestión completa de torneos de ajedrez.

## Demo en producción

[ajedrezcoimbra.com/calendario-de-eventos/](https://ajedrezcoimbra.com/calendario-de-eventos/)

## Instalación

1. Sube la carpeta `dp-torneos` a `/wp-content/plugins/`
2. Activa el plugin desde **Plugins → Plugins instalados**
3. Las tablas se crean automáticamente al activar

## Uso

### Panel de administración
Ve a **♟ Torneos** en el menú lateral de WordPress.

- **Dashboard** — estadísticas generales e inscripciones recientes
- **Eventos** — listado, alta, edición y borrado de torneos
- **Inscripciones** — gestión de inscritos por evento, exportar PDF y CSV
- **Ajustes** — email de notificaciones, moneda

### Shortcodes

| Shortcode | Descripción |
|---|---|
| `[dp_torneo_calendario]` | Calendario público de eventos |
| `[dp_torneo_calendario provincia="Murcia" limite="10"]` | Filtrado por provincia |
| `[dp_torneo_inscripcion id="X"]` | Formulario de inscripción de un evento |
| `[dp_torneo_ficha id="X"]` | Ficha pública completa de un evento |

> El shortcode de cada evento se genera automáticamente y es visible en el listado de eventos del panel de administración.

### Flujo recomendado

1. Crear el evento con estado **Borrador**
2. Revisar y cambiar a **Abierto** cuando quieras publicarlo
3. Pegar `[dp_torneo_calendario]` en la página de torneos de tu web
4. O pegar `[dp_torneo_inscripcion id="X"]` directamente en una página del evento
5. Gestionar inscritos desde **Torneos → Inscripciones**
6. Exportar PDF o CSV desde el listado de inscritos
7. Cerrar inscripciones cambiando el estado a **Cerrado**

## Tablas de base de datos

- `{prefix}dp_eventos` — torneos y eventos
- `{prefix}dp_eventos_inscripciones` — inscripciones de socios

Al **eliminar** el plugin desde WordPress, las tablas y opciones se borran automáticamente.

### Esquema SQL
```sql
-- ──────────────────────────────────────────────────
--  TABLA: dp_eventos
-- ──────────────────────────────────────────────────
CREATE TABLE dp_eventos (
    id                        INT UNSIGNED                            NOT NULL AUTO_INCREMENT,
    nombre                    VARCHAR(255)                            NOT NULL,
    modalidad                 ENUM('Individual', 'Por Equipos')       NOT NULL,
    cuota_inscripcion         DECIMAL(8,2)                            NULL        COMMENT 'En euros. NULL = gratuito',
    numero_rondas             TINYINT UNSIGNED                        NULL,
    poblacion                 VARCHAR(150)                            NOT NULL,
    provincia                 VARCHAR(100)                            NOT NULL,
    fecha_inicio              DATE                                    NOT NULL    COMMENT 'Inicio del torneo',
    fecha_fin                 DATE                                    NOT NULL    COMMENT 'Fin del torneo',
    fecha_inicio_inscripcion  DATE                                    NOT NULL    COMMENT 'Apertura de inscripciones',
    fecha_fin_inscripcion     DATE                                    NOT NULL    COMMENT 'Cierre de inscripciones',
    estado                    ENUM('borrador', 'abierto', 'cerrado')  NOT NULL    DEFAULT 'borrador',
    url_bases                 VARCHAR(500)                            NULL,
    google_maps               VARCHAR(500)                            NULL        COMMENT 'URL de Google Maps o coordenadas',
    created_at                DATETIME                                NOT NULL    DEFAULT CURRENT_TIMESTAMP,
    updated_at                DATETIME                                NOT NULL    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_estado             (estado),
    INDEX idx_fecha_inicio       (fecha_inicio),
    INDEX idx_fecha_fin_insc     (fecha_fin_inscripcion),
    INDEX idx_provincia          (provincia)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Torneos y eventos de ajedrez';

-- ──────────────────────────────────────────────────
--  TABLA: dp_eventos_inscripciones
-- ──────────────────────────────────────────────────
CREATE TABLE dp_eventos_inscripciones (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    evento_id     INT UNSIGNED    NOT NULL,
    nombre        VARCHAR(100)    NOT NULL,
    apellidos     VARCHAR(150)    NOT NULL,
    fide_id       VARCHAR(20)     NULL        COMMENT 'ID FIDE, opcional',
    telefono      VARCHAR(30)     NULL,
    email         VARCHAR(255)    NULL,
    alojamiento   TINYINT(1)      NOT NULL    DEFAULT 0   COMMENT '0 = No, 1 = Sí',
    created_at    DATETIME        NOT NULL    DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_evento_id   (evento_id),
    INDEX idx_fide_id     (fide_id),
    CONSTRAINT fk_inscripcion_evento
        FOREIGN KEY (evento_id)
            REFERENCES dp_eventos(id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Inscripciones a torneos de ajedrez';
```

## Requisitos

- WordPress 5.8+
- PHP 7.4+
- MySQL 5.7+ / MariaDB 10.3+