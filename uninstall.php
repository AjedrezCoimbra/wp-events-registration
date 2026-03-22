<?php
// Solo se ejecuta al eliminar el plugin desde el panel de WordPress
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

global $wpdb;

// Eliminar tablas
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wper_inscripciones" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wper_eventos" );

// Eliminar opciones nuevas
delete_option( 'wper_version' );
delete_option( 'wper_email_admin' );
delete_option( 'wper_email_notificar' );
delete_option( 'wper_moneda' );
delete_option( 'wper_auto_updates' );
delete_option( 'wper_github_token' );

// No quedan opciones antiguas que limpiar
