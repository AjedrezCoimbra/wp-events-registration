<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPER_Public {

    public function init() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    public function enqueue_assets() {
        // Solo cargar si la página tiene un shortcode del plugin
        global $post;
        if ( ! is_a( $post, 'WP_Post' ) ) return;

        $has_shortcode =
            has_shortcode( $post->post_content, 'wper_calendario' ) ||
            has_shortcode( $post->post_content, 'wper_inscripcion' ) ||
            has_shortcode( $post->post_content, 'wper_ficha' ) ||
            has_shortcode( $post->post_content, 'wper_test' );

        if ( ! $has_shortcode ) return;

        wp_enqueue_style(
            'wper-public',
            WPER_PLUGIN_URL . 'public/assets/public.css',
            array(),
            WPER_VERSION
        );

        wp_enqueue_script(
            'wper-public',
            WPER_PLUGIN_URL . 'public/assets/public.js',
            array('jquery'),
            WPER_VERSION,
            true
        );

        wp_localize_script( 'wper-public', 'wperData', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'wper_inscribir_nonce' ),
            'i18n'     => array(
                'enviando'  => __( 'Enviando...', 'wp-events-registration' ),
                'error_gen' => __( 'Error al procesar la inscripción.', 'wp-events-registration' ),
            ),
        ) );
    }
}
