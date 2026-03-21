<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPER_Deactivator {
    public static function deactivate() {
        // No borramos tablas al desactivar (solo al desinstalar)
        // Limpiamos transients si los hubiera
        delete_transient( 'wper_stats' );
    }
}
