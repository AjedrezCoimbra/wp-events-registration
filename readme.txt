=== WP Events Registration ===
Contributors: ajedrezcoimbra
Tags: events, registration, chess, tournaments, calendar, inscriptions
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 2.0.3
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Gestión completa de eventos de ajedrez: inscripciones, calendario público con 3 pestañas y exportación PDF/CSV.

== Description ==

Plugin de gestión de eventos de ajedrez y sus inscripciones para sitios de WordPress. Creado, optimizado y diseñado por el Club de Ajedrez Coimbra.

= Funcionalidades principales =

* **Calendario público** con 3 pestañas automáticas: Inscripción Abierta, Cerrada y Finalizados
* **Formulario de inscripción** vía AJAX con modal y confirmación por email
* **Panel de administración** con dashboard de estadísticas, lista de eventos, inscripciones y exportación
* **Exportación PDF y CSV** de participantes por evento
* **Actualizaciones automáticas** desde GitHub Releases
* **Plantillas de email** configurables con variables dinámicas
* **Clasificación inteligente** de eventos basada en fechas (abierto, cerrado, finalizado)

= Shortcodes =

* `[wper_calendario]` — Calendario público con tarjetas visuales y 3 pestañas
* `[wper_inscripcion id="X"]` — Formulario de inscripción para un evento
* `[wper_ficha id="X"]` — Ficha pública completa del evento con mapa y formulario

== Installation ==

1. Upload the `wp-events-registration` folder to `/wp-content/plugins/`
2. Activate the plugin through the **Plugins** screen in WordPress
3. The database tables are created automatically upon activation
4. Add the shortcode `[wper_calendario]` to any page to show the public calendar

For automatic updates from GitHub, no additional configuration is required.

== Frequently Asked Questions ==

= How do I display the calendar on a page? =

Use the shortcode `[wper_calendario]` on any page or post. Optional attributes: `provincia="Murcia"` and `limite="10"`.

= Can users register without being logged in? =

Yes. Registration is public and does not require a WordPress account. Email is required.

= How are events classified in the calendar? =

Automatically by dates: open registration (deadline not passed), closed (in progress but not accepting registrations), and finished (event end date passed).

= Is there a payment gateway? =

No. The fee field is informational only. No payment gateway is integrated.

== Screenshots ==

1. Public calendar with 3 tabs and event cards
2. Registration form modal
3. Admin dashboard with statistics
4. Event creation form

== Changelog ==

= 2.0.3 =
* Fix: Duplicate email check now prevents multiple registrations with the same email
* Fix: Currency symbol now respects the configured currency (EUR/USD/GBP)
* Fix: `permitir_inscripcion_web` toggle now saves correctly
* Fix: Province and limit attributes now work in `[wper_calendario]`
* Fix: Nonce separation for registration and participant list AJAX actions
* Fix: GitHub API response code validation
* Fix: CC/BCC email validation in settings
* Tweak: Rate limiting on public registration endpoint (5/hour per IP)
* Tweak: i18n coverage for admin strings
* Tweak: Removed unused code and dead variables
* Requires PHP: 7.4 header added

= 2.0.2 =
* Fix: Minor security hardening and code cleanup

= 2.0.1 =
* Fix: "Petición no válida" error when viewing participant list from public calendar (missing nonce)

= 2.0.0 =
* Major refactor and security audit
* PDF export based on native HTML (no external libraries)
* Pagination on all listings
* Nonce validation on all AJAX calls
* Strict sanitization and post-closure registration protection
* Centralized event status logic
* Full cleanup on uninstall

== Upgrade Notice ==

= 2.0.3 =
Fixes duplicate registration, currency symbol, and adds rate limiting. Recommended update for all users.
