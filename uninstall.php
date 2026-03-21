<?php
// Solo se ejecuta al eliminar el plugin desde el panel de WordPress
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

global $wpdb;

// Eliminar tablas
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}dp_eventos_inscripciones" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}dp_eventos" );

// Eliminar opciones del plugin
delete_option( 'dp_torneos_version' );
delete_option( 'dp_torneos_email_admin' );
delete_option( 'dp_torneos_email_notificar' );
delete_option( 'dp_torneos_moneda' );
