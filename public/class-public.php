<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class DP_Torneos_Public {

    public function init() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    public function enqueue_assets() {
        // Solo cargar si la página tiene un shortcode del plugin
        global $post;
        if ( ! is_a( $post, 'WP_Post' ) ) return;

        $has_shortcode =
            has_shortcode( $post->post_content, 'dp_torneo_calendario' ) ||
            has_shortcode( $post->post_content, 'dp_torneo_inscripcion' ) ||
            has_shortcode( $post->post_content, 'dp_torneo_ficha' );

        if ( ! $has_shortcode ) return;

        wp_enqueue_style(
            'dp-torneos-public',
            DP_TORNEOS_PLUGIN_URL . 'public/assets/public.css',
            array(),
            DP_TORNEOS_VERSION
        );

        wp_enqueue_script(
            'dp-torneos-public',
            DP_TORNEOS_PLUGIN_URL . 'public/assets/public.js',
            array('jquery'),
            DP_TORNEOS_VERSION,
            true
        );

        wp_localize_script( 'dp-torneos-public', 'dpTorneos', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'dp_inscribir_nonce' ),
            'i18n'     => array(
                'enviando'  => __( 'Enviando...', 'dp-torneos' ),
                'error_gen' => __( 'Error al procesar la inscripción.', 'dp-torneos' ),
            ),
        ) );
    }
}
