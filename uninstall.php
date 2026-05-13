<?php
// Solo se ejecuta al eliminar el plugin desde el panel de WordPress
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

global $wpdb;

// 1. Eliminar tablas
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wper_inscripciones" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wper_eventos" );

// 2. Eliminar todas las opciones (incluyendo plantillas de email)
$options = array(
    'wper_version',
    'wper_email_admin',
    'wper_email_notificar',
    'wper_moneda',
    'wper_auto_updates',
    'wper_github_token',
    'wper_email_confirmacion_asunto',
    'wper_email_confirmacion_cuerpo',
    'wper_email_confirmacion_cc',
    'wper_email_confirmacion_bcc',
    'wper_email_notificacion_para',
    'wper_email_notificacion_asunto',
    'wper_email_notificacion_cuerpo',
    'wper_email_notificacion_cc',
    'wper_email_notificacion_bcc'
);

foreach ( $options as $opt ) {
    delete_option( $opt );
}

// 3. Limpiar transientes
delete_site_transient( 'update_plugins' );
delete_transient( 'wper_github_release_cache' );

// 4. Cancelar tarea programada
wp_clear_scheduled_hook( 'wper_daily_auto_close' );
