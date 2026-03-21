<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class DP_Torneos_Deactivator {
    public static function deactivate() {
        // No borramos tablas al desactivar (solo al desinstalar)
        // Limpiamos transients si los hubiera
        delete_transient( 'dp_torneos_stats' );
    }
}
