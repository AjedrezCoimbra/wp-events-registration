# WP Events Registration (v2.0.1)

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

- **Dashboard** — Estadísticas generales e inscripciones recientes. Clasificación unificada de eventos en **Abiertos, Cerrados y Finalizados**.
- **Eventos** — Gestión completa de eventos (alta, edición con editor visual, borrado, subida de carteles). El formulario está organizado en secciones: *Datos generales*, *Fechas*, *Enlaces y multimedia* y *Observaciones*. Desde la lista se puede filtrar por el estado real del evento basado en fechas.
- **Inscripciones** — Listado de inscritos, exportar PDF y CSV.
- **Ajustes** — Configuración de email de notificaciones, moneda, **plantillas enriquecidas de correo** (con variables `{{nombre}}`, `{{evento_nombre}}`, `{{evento_cuota}}`, `{{evento_lugar}}`, etc.) y forzado de comprobación de actualizaciones.

### Shortcodes

| Shortcode | Descripción |
|---|---|
| `[wper_calendario]` | Calendario público con tarjetas visuales organizadas por **3 Tabs (Abiertos, Cerrados y Finalizados)**. La clasificación es automática por fechas. Muestra ritmo, tiempo, sello ELO FIDE y marca de **Subvencionable**. Abre el **formulario de inscripción en ventana modal**. |
| `[wper_inscripcion id="X"]` | Formulario dinámico de inscripción para el evento con ID específico. El email es obligatorio. |
| `[wper_ficha id="X"]` | Ficha pública completa con todos los detalles técnicos (rondas, ritmo, tiempo, ELO FIDE), mapa y formulario integrado. |

### Clasificación de Eventos (Calendario y Admin)

El plugin organiza los eventos automáticamente según sus fechas y estado en todas las vistas:
- **Inscripción Abierta**: Eventos marcados como "Abiertos" donde la fecha actual es anterior o igual a la *Fecha fin de inscripción*.
- **Inscripción Cerrada**: Eventos que ya no permiten inscribirse pero que **aún no han terminado** (la fecha actual es posterior al cierre de inscripción pero anterior o igual a la *Fecha fin del evento*).
- **Eventos Finalizados**: Eventos cuya fecha de finalización ya ha pasado.

### Botones en la tarjeta pública

Cada tarjeta del calendario puede mostrar hasta cuatro botones de acción según la configuración del evento:

| Botón | Cuándo aparece |
|---|---|
| **Ver bases** | Si el campo *URL de las bases* está relleno |
| **Inscribirse** | Si el evento permite inscripciones (Tab de Abiertos) |
| **Ver inscritos** | Siempre en eventos abiertos/cerrados (muestra la lista interna de la BD) |
| **Info64/ChessResults** | Si el campo *URL lista de inscritos (externa)* está relleno |

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
    INDEX idx_fecha_fin (fecha_fin),
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
| **2.0.1** | **FIX QUIRÚRGICO**: Corregido error "Petición no válida" al intentar ver el listado de inscritos desde el calendario público. El error se debía a la falta del token de seguridad (nonce) en la petición AJAX. |
| **2.0.0** | **AUDITORÍA COMPLETA Y REFACTORIZACIÓN MAYOR** · Nueva implementación de **PDF basada en HTML nativo** (adiós a problemas de fuentes y memoria) · **Optimización de rendimiento**: Paginación en todos los listados y eliminación de problema N+1 queries · **Seguridad reforzada**: Validación de nonces en todas las llamadas AJAX, sanitización estricta y protección de inscripciones post-cierre · **Paginación premium** rediseñada para una mejor experiencia de usuario · Lógica de estados de evento centralizada · Limpieza total al desinstalar · Corrección de múltiples bugs y avisos de seguridad. |
| **1.7.0** | Botón externo renombrado a 'Info64/ChessResults' · Fix cierre modal inscripción · PDF sin caracteres raros (conversión UTF-8 unificada) · Fix fecha inscripción en ficha pública · Eliminados botones Cerrar/Abrir del listado admin · Campo "Para" de notificación usa Email de Ajustes · Nueva columna "Fin evento" en listado admin · Nuevas variables en plantillas de correo (modalidad, cuota, ritmo, cartel_url…) · Carpeta .claude eliminada del repositorio |
| **1.6.6** | Mejora visual en Calendario: Etiqueta FINALIZADO por fechas · Renombrado botón de listado externo a 'Ver todos los inscritos Info64/ChessResults' |
| **1.6.5** | Unificación de clasificación en el Panel Admin (Dashboard y Listado) · Nuevos filtros por estado real · Contador de finalizados · Estilos corregidos en el admin |
| **1.6.0** | Nueva clasificación de 3 pestañas (Abiertos, Cerrados, Finalizados) basada en fechas · Paginación independiente para cerrados/finalizados · Eliminado filtro de provincia · Mejoras visuales en el calendario |
| **1.5.2** | Eliminado campo *URL inscripciones externa*; se mantiene solo *URL lista de inscritos* |
| **1.5.1** | Nuevo campo *URL lista de inscritos (externa)* para Chess-Results, Info64, etc. |
| **1.5.0** | Fix bug alojamiento · Email obligatorio en inscripción · Duplicar evento · Toggle estado rápido · Secciones en formulario admin |
| **1.4.2** | Mejoras menores |
| **1.4.0** | Tabs abiertos/cerrados en calendario · Modal de inscripción |
