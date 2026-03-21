# WP Events Registration

Plugin de gestión de eventos e inscripciones para sitios de WordPress, alineado con el Club de Ajedrez Coimbra de Jumilla.

## Instalación

1. Sube la carpeta `wp-events-registration` a `/wp-content/plugins/`
2. Activa el plugin desde **Plugins → Plugins instalados**
3. Las tablas se crean automáticamente al activar con el prefijo `{wp_prefix}wper_`

## Uso

### Panel de administración
Ve a **♟ Eventos** en el menú lateral de WordPress.

- **Dashboard** — Estadísticas generales e inscripciones recientes.
- **Eventos** — Gestión completa de eventos (alta, edición, borrado, observaciones).
- **Inscripciones** — Listado de inscritos, exportar PDF y CSV.
- **Ajustes** — Configuración de notificaciones y preferencias.

### Shortcodes

| Shortcode | Descripción |
|---|---|
| `[wper_calendario]` | Calendario público de eventos filtrado por estado 'abierto' |
| `[wper_calendario provincia="Murcia" limite="10"]` | Filtrado avanzado por provincia y límite de visualización |
| `[wper_inscripcion id="X"]` | Formulario dinámico de inscripción para el evento con ID específico |
| `[wper_ficha id="X"]` | Ficha pública completa del evento con mapa y detalles detallados |

## Actualizaciones Automáticas

Este plugin incluye un sistema de actualización nativo integrado con **GitHub Releases**. 

Para lanzar una nueva actualización y que aparezca en el panel de WordPress de los sitios instalados:
1. **Actualiza la versión** en el código local (encabezado de `wp-events-registration.php` y constante `WPER_VERSION`).
2. **Crea un Release** en este repositorio de GitHub con el mismo número de versión (ej: `v1.1.1`).
3. WordPress detectará automáticamente la nueva versión y permitirá actualizar con un solo clic.

## Estructura de Datos (SQL)

El plugin utiliza dos tablas personalizadas con integridad referencial.

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
    observaciones             TEXT             NULL,
    created_at                DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
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
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_wper_inscripcion_evento
        FOREIGN KEY (evento_id)
        REFERENCES {prefix}wper_eventos(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
```

## Arquitectura del Plugin

El plugin sigue una estructura modular orientada a objetos (OOP) inspirada en el WordPress Plugin Boilerplate:

- **`admin/`**: Contiene la lógica y vistas exclusivas del panel de control de WordPress.
- **`public/`**: Gestiona la lógica de frontend, activos (CSS/JS) y renderizado de shortcodes.
- **`includes/`**: Clases de utilidad centralizadas:
    - `WPER_DB`: Capa de abstracción de base de datos (CRUD).
    - `WPER_PDF`: Generación de listados de inscritos en PDF.
    - `WPER_Shortcodes`: Definición y lógica de los shortcodes disponibles.
    - `WPER_Activator`/`WPER_Deactivator`: Gestión del ciclo de vida del plugin.
- **`wp-events-registration.php`**: Archivo núcleo que inicializa el plugin y carga sus componentes.

## Requisitos
- WordPress 5.8+
- PHP 7.4+
- MySQL 5.7+ / MariaDB 10.3+