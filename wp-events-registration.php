<?php
/**
 * Plugin Name:       WP Events Registration
 * Plugin URI:        https://github.com/AjedrezCoimbra/wp-events-registration
 * Description:       Gestión completa de eventos: inscripciones, calendario público y exportación PDF.
 * Version:           1.2.0
 * Author:            José Joaquín Sánchez Fernández
 * Author URI:        https://ajedrezcoimbra.com
 * License:           GPL-2.0+
 * Text Domain:       wp-events-registration
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ── Constantes ──────────────────────────────────────────────
define( 'WPER_VERSION',    '1.2.0' );
define( 'WPER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPER_PLUGIN_FILE', __FILE__ );

// ── Autoload de clases ───────────────────────────────────────
require_once WPER_PLUGIN_DIR . 'includes/class-activator.php';
require_once WPER_PLUGIN_DIR . 'includes/class-deactivator.php';
require_once WPER_PLUGIN_DIR . 'includes/class-db.php';
require_once WPER_PLUGIN_DIR . 'includes/class-pdf.php';
require_once WPER_PLUGIN_DIR . 'includes/class-shortcodes.php';
require_once WPER_PLUGIN_DIR . 'admin/class-admin.php';
require_once WPER_PLUGIN_DIR . 'public/class-public.php';
require_once WPER_PLUGIN_DIR . 'includes/class-updater.php';

// ── Activación / Desactivación ───────────────────────────────
register_activation_hook( __FILE__,   array( 'WPER_Activator',   'activate' ) );
register_deactivation_hook( __FILE__, array( 'WPER_Deactivator', 'deactivate' ) );

// ── Arrancar el plugin ───────────────────────────────────────
function wper_run() {
    // Comprobar si hay que actualizar la base de datos
    $db_version = get_option( 'wper_version', '1.0.0' );
    if ( version_compare( $db_version, WPER_VERSION, '<' ) ) {
        WPER_Activator::activate(); // Vuelve a correr dbDelta para añadir nuevas columnas
        update_option( 'wper_version', WPER_VERSION );
    }

    $admin  = new WPER_Admin();
    $public = new WPER_Public();
    $sc     = new WPER_Shortcodes();
    $update = new WPER_Updater( WPER_PLUGIN_FILE, WPER_VERSION, 'https://github.com/AjedrezCoimbra/wp-events-registration' );

    $admin->init();
    $public->init();
    $sc->init();
    $update->init();
}
add_action( 'plugins_loaded', 'wper_run' );
