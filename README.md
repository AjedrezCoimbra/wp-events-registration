# WP Events Registration (v1.5.2)

Plugin de gestión de eventos de ajedrez y sus inscripciones para sitios de WordPress.
Creado, optimizado y diseñado por el Club de Ajedrez Coimbra.

## Instalación

1. Sube la carpeta `wp-events-registration` a `/wp-content/plugins/`
2. Activa el plugin desde **Plugins → Plugins instalados**
3. Las tablas se crean automáticamente al activar con el prefijo `{wp_prefix}wper_`
4. Al actualizar el plugin, la migración de base de datos se ejecuta automáticamente sin necesidad de reactivar.

## Actualizaciones automáticas

El plugin soporta actualizaciones automáticas directamente desde GitHub:
- Las nuevas versiones se publican como **GitHub Releases** y WordPress las detecta automáticamente cada 12 horas.
- Puedes activar o desactivar las actualizaciones automáticas desde el enlace **"Activar las actualizaciones automáticas"** en el listado de plugins de WordPress.
- Si hay una nueva versión disponible, aparecerá el aviso estándar de WordPress permitiendo actualizar con un solo clic.

## Uso

### Panel de administración
Ve a **Eventos** en el menú lateral de WordPress.

- **Dashboard** — Estadísticas generales e inscripciones recientes.
- **Eventos** — Gestión completa de eventos (alta, edición con editor visual, borrado, subida de carteles). El formulario está organizado en secciones: *Datos generales*, *Fechas*, *Enlaces y multimedia* y *Observaciones*. Desde la lista se puede **abrir/cerrar** un evento con un clic y **duplicar** un evento como borrador.
- **Inscripciones** — Listado de inscritos, exportar PDF y CSV.
- **Ajustes** — Configuración de email de notificaciones, moneda, **plantillas enriquecidas de correo** (con variables `{{nombre}}`, `{{evento_nombre}}`, etc.) y forzado de comprobación de actualizaciones.

### Shortcodes

| Shortcode | Descripción |
|---|---|
| `[wper_calendario]` | Calendario público con tarjetas visuales organizadas por **Tabs (Abiertos y Cerrados)**. Muestra ritmo, tiempo, sello ELO FIDE y marca de **Subvencionable** si están configurados. Abre el **formulario de inscripción en ventana modal** facilitando el flujo de usuario. |
| `[wper_calendario provincia="Murcia" limite="10"]` | Filtrado avanzado por provincia y límite de visualización. |
| `[wper_inscripcion id="X"]` | Formulario dinámico de inscripción para el evento con ID específico. El email es obligatorio. |
| `[wper_ficha id="X"]` | Ficha pública completa con todos los detalles técnicos (rondas, ritmo, tiempo, ELO FIDE), mapa y formulario integrado. |

### Botones en la tarjeta pública

Cada tarjeta del calendario puede mostrar hasta cuatro botones de acción según la configuración del evento:

| Botón | Cuándo aparece |
|---|---|
| **Ver bases** | Si el campo *URL de las bases* está relleno |
| **Inscribirse** | Si el evento está abierto (abre modal con formulario interno) |
| **Ver inscritos** | Siempre en eventos abiertos/cerrados (muestra la lista interna de la BD) |
| **Ver inscritos (ext.)** | Si el campo *URL lista de inscritos (externa)* está relleno (Chess-Results, Info64...) |

## Campos de un evento

| Campo | Descripción |
|---|---|
| Nombre | Nombre del torneo o evento |
| Modalidad | Individual / Por Equipos |
| Estado | Borrador (oculto) / Abierto / Cerrado |
| Población / Provincia | Localización del evento |
| Número de rondas | |
| Tiempo de juego | Texto libre, p.ej. `90' + 30''` |
| Ritmo de juego | Clásico / Rápido / Blitz |
| ELO FIDE | Marca si es válido para ELO FIDE |
| Subvencionable | Marca si el evento es subvencionable |
| Cuota de inscripción | En euros; vacío = gratuito |
| Fecha inicio / fin | Duración del evento |
| Fin de inscripción | Cierre automático por WP-Cron al llegar esta fecha |
| URL de las bases | Enlace al reglamento / bases |
| **URL lista de inscritos (externa)** | Enlace externo con la lista de participantes (Chess-Results, Info64…) |
| Google Maps | URL del mapa del lugar |
| Imagen del cartel | Sube desde la biblioteca de medios |
| Observaciones | Editor visual con HTML enriquecido |
| Enviar email de confirmación | Si está activo, se envía email al inscribirse |

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
    tiempo_juego              VARCHAR(30)      DEFAULT NULL,
    elo_fide                  TINYINT(1)       DEFAULT 0,
    ritmo_juego               VARCHAR(10)      DEFAULT NULL,
    poblacion                 VARCHAR(150)     NOT NULL,
    provincia                 VARCHAR(100)     NOT NULL,
    fecha_inicio              DATE             NOT NULL,
    fecha_fin                 DATE             NOT NULL,
    fecha_inicio_inscripcion  DATE             NOT NULL,
    fecha_fin_inscripcion     DATE             NOT NULL,
    estado                    ENUM('borrador','abierto','cerrado') NOT NULL DEFAULT 'borrador',
    url_bases                 VARCHAR(500)     NULL,
    url_inscritos             VARCHAR(500)     NULL,
    google_maps               VARCHAR(500)     NULL,
    cartel_url                VARCHAR(500)     NULL,
    observaciones             TEXT             NULL,
    subvencionable            TINYINT(1)       NOT NULL DEFAULT 0,
    enviar_confirmacion       TINYINT(1)       NOT NULL DEFAULT 1,
    created_at                DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_estado (estado),
    INDEX idx_fecha_inicio (fecha_inicio),
    INDEX idx_fecha_fin_insc (fecha_fin_inscripcion),
    INDEX idx_provincia (provincia)
) ENGINE=InnoDB;
```

### 2. Tabla: `wper_inscripciones`
Gestiona los participantes inscritos vinculados a un evento.
```sql
CREATE TABLE {prefix}wper_inscripciones (
    id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    evento_id     INT UNSIGNED  NOT NULL,
    nombre        VARCHAR(100)  NOT NULL,
    apellidos     VARCHAR(150)  NOT NULL,
    fide_id       VARCHAR(20)   NULL,
    telefono      VARCHAR(30)   NULL,
    email         VARCHAR(255)  NOT NULL,
    alojamiento   TINYINT(1)    NOT NULL DEFAULT 0,
    observaciones TEXT          NULL,
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
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
- **`includes/`**: Clases núcleo (DB, PDF, Shortcodes, Activación, Updater GitHub).

## Requisitos
- WordPress 5.8+
- PHP 7.4+ (requiere `ext-json` y `ext-mbstring` para PDF)
- MySQL 5.7+ / MariaDB 10.3+

## Historial de versiones

| Versión | Cambios |
|---|---|
| **1.5.2** | Eliminado campo *URL inscripciones externa*; se mantiene solo *URL lista de inscritos* |
| **1.5.1** | Nuevo campo *URL lista de inscritos (externa)* para Chess-Results, Info64, etc. |
| **1.5.0** | Fix bug alojamiento · Email obligatorio en inscripción · Duplicar evento · Toggle estado rápido · Secciones en formulario admin |
| **1.4.2** | Mejoras menores |
| **1.4.0** | Tabs abiertos/cerrados en calendario · Modal de inscripción |
