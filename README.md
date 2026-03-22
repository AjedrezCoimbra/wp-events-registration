# WP Events Registration

Plugin de gestión de eventos de ajedrez y sus inscripciones para sitios de WordPress.
Creado, optimizado y diseñado por José Joaquín Sánchez Fernández para el Club de Ajedrez Coimbra.

## Instalación

1. Sube la carpeta `wp-events-registration` a `/wp-content/plugins/`
2. Activa el plugin desde **Plugins → Plugins instalados**
3. Las tablas se crean automáticamente al activar con el prefijo `{wp_prefix}wper_`

## Novedades v1.2.6

- **Refactorización completa**: Todos los componentes usan ahora el prefijo `wper_` (eliminada la compatibilidad con `dp_*`).
- **Vista de Calendario Mejorada**:
  - Modal moderno para ver observaciones enriquecidas.
  - Botón de mapa profesional.
  - Imagen de cartel por defecto (temática ajedrez) para eventos sin imagen.
  - Formato de fechas amigable: "Del DD/MM/AAAA hasta DD/MM/AAAA".
- **Gestión de Inscripciones**: Nuevo campo de observaciones en el formulario de inscripción.
- **Auto-cierre inteligente**: Los eventos se cierran automáticamente cuando pasa la fecha límite de inscripción.

## Uso

### Panel de administración
Ve a **♟ Eventos** en el menú lateral de WordPress.

- **Dashboard** — Estadísticas generales e inscripciones recientes.
- **Eventos** — Gestión completa de eventos (alta, edición con editor visual, borrado, subida de carteles).
- **Inscripciones** — Listado de inscritos, exportar PDF y CSV.
- **Ajustes** — Configuración de email de notificaciones y moneda.

### Shortcodes

| Shortcode | Descripción |
|---|---|
| `[wper_calendario]` | Calendario público de eventos con tarjetas visuales, búsqueda por provincia y modal de info. |
| `[wper_calendario provincia="Murcia" limite="10"]` | Filtrado avanzado por provincia y límite de visualización. |
| `[wper_inscripcion id="X"]` | Formulario dinámico de inscripción para el evento con ID específico. |
| `[wper_ficha id="X"]` | Ficha pública completa del evento con mapa integrado y detalles. |

## Estructura de Datos (SQL)

El plugin utiliza dos tablas con integridad referencial e índices para optimizar el rendimiento.

### 1. Tabla: `wper_eventos`
Almacena la configuración y detalles de cada evento.
```sql
CREATE TABLE {prefix}wper_eventos (
    id                        INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    nombre                    VARCHAR(255)     NOT NULL,
    modalidad                 ENUM('Individual','Por Equipos') NOT NULL,
    cuota_inscripcion         DECIMAL(8,2)     NULL,
    numero_rondas             TINYINT UNSIGNED NULL,
    poblacion                 VARCHAR(150)     NOT NULL,
    provincia                 VARCHAR(100)     NOT NULL,
    fecha_inicio              DATE             NOT NULL,
    fecha_fin                 DATE             NOT NULL,
    fecha_inicio_inscripcion  DATE             NOT NULL,
    fecha_fin_inscripcion     DATE             NOT NULL,
    estado                    ENUM('borrador','abierto','cerrado') NOT NULL DEFAULT 'borrador',
    url_bases                 VARCHAR(500)     NULL,
    google_maps               VARCHAR(500)     NULL,
    cartel_url                VARCHAR(500)     NULL,
    observaciones             TEXT             NULL,
    created_at                DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_estado (estado),
    INDEX idx_fecha_inicio (fecha_inicio)
) ENGINE=InnoDB;
```

### 2. Tabla: `wper_inscripciones`
Gestiona los participantes inscritos vinculados a un evento.
```sql
CREATE TABLE {prefix}wper_inscripciones (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    evento_id   INT UNSIGNED  NOT NULL,
    nombre      VARCHAR(100)  NOT NULL,
    apellidos   VARCHAR(150)  NOT NULL,
    fide_id     VARCHAR(20)   NULL,
    telefono    VARCHAR(30)   NULL,
    email       VARCHAR(255)  NULL,
    alojamiento TINYINT(1)    NOT NULL DEFAULT 0,
    observaciones TEXT        NULL,
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_wper_inscripcion_evento
        FOREIGN KEY (evento_id)
        REFERENCES {prefix}wper_eventos(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
```

## Arquitectura

- **`admin/`**: Lógica de gestión, exportación CSV/PDF y vistas de gestión.
- **`public/`**: Frontend (CSS moderno, JS asíncrono para inscripciones).
- **`includes/`**: Clases núcleo (DB, PDF, Shortcodes, Activación).

## Requisitos
- WordPress 5.8+
- PHP 7.4+ (requiere `ext-json` y `ext-mbstring` para PDF)
- MySQL 5.7+ / MariaDB 10.3+