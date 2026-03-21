# Pluggin de Gestión de Torneos de Ajedrez

Plugin de WordPress del Club de Ajedrez Coimbra de Jumilla para la gestión completa de torneos de ajedrez.

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

## Requisitos

- WordPress 5.8+
- PHP 7.4+
- MySQL 5.7+ / MariaDB 10.3+
