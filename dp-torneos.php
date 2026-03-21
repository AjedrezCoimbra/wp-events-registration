<?php
/**
 * Plugin Name:       Dirección Deportiva - Eventos
 * Plugin URI:        https://ajedrezcoimbra.com
 * Description:       Gestión completa de torneos de ajedrez: eventos, inscripciones, calendario público y exportación PDF.
 * Version:           1.0.0
 * Author:            José Joaquín Sánchez Fernández
 * Author URI:        https://ajedrezcoimbra.com
 * License:           GPL-2.0+
 * Text Domain:       dp-torneos
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ── Constantes ──────────────────────────────────────────────
define( 'DP_TORNEOS_VERSION',   '1.0.0' );
define( 'DP_TORNEOS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DP_TORNEOS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'DP_TORNEOS_PLUGIN_FILE', __FILE__ );

// ── Autoload de clases ───────────────────────────────────────
require_once DP_TORNEOS_PLUGIN_DIR . 'includes/class-activator.php';
require_once DP_TORNEOS_PLUGIN_DIR . 'includes/class-deactivator.php';
require_once DP_TORNEOS_PLUGIN_DIR . 'includes/class-db.php';
require_once DP_TORNEOS_PLUGIN_DIR . 'includes/class-pdf.php';
require_once DP_TORNEOS_PLUGIN_DIR . 'includes/class-shortcodes.php';
require_once DP_TORNEOS_PLUGIN_DIR . 'admin/class-admin.php';
require_once DP_TORNEOS_PLUGIN_DIR . 'public/class-public.php';

// ── Activación / Desactivación ───────────────────────────────
register_activation_hook( __FILE__,   array( 'DP_Torneos_Activator',   'activate' ) );
register_deactivation_hook( __FILE__, array( 'DP_Torneos_Deactivator', 'deactivate' ) );

// ── Arrancar el plugin ───────────────────────────────────────
function dp_torneos_run() {
    $admin  = new DP_Torneos_Admin();
    $public = new DP_Torneos_Public();
    $sc     = new DP_Torneos_Shortcodes();

    $admin->init();
    $public->init();
    $sc->init();
}
add_action( 'plugins_loaded', 'dp_torneos_run' );
