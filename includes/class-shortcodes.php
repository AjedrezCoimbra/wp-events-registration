<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPER_Shortcodes {

    public function init() {
        // Registrar directamente para asegurar que están disponibles lo antes posible
        $this->register_shortcodes();
        
        // También en el gancho init por si acaso el orden de carga de los plugins varía
        add_action( 'init', array( $this, 'register_shortcodes' ) );
        
        add_action( 'wp_ajax_wper_inscribir',        array( $this, 'ajax_inscribir' ) );
        add_action( 'wp_ajax_nopriv_wper_inscribir', array( $this, 'ajax_inscribir' ) );
    }

    public function register_shortcodes() {
        // Estándar
        add_shortcode( 'wper_calendario',   array( $this, 'shortcode_calendario' ) );
        add_shortcode( 'wper_inscripcion',  array( $this, 'shortcode_inscripcion' ) );
        add_shortcode( 'wper_ficha',        array( $this, 'shortcode_ficha' ) );
        add_shortcode( 'wper_test',         function() { return 'Plugin WP Events Registration: OK'; } );
    }

    // ══════════════════════════════════════════════════════
    //  [wper_calendario]
    //  Atributos: provincia, limite
    // ══════════════════════════════════════════════════════
    public function shortcode_calendario( $atts ) {
        $atts = shortcode_atts( array(
            'provincia' => '',
            'limite'    => 20,
        ), $atts, 'wper_calendario' );

        $args = array(
            'limite'    => intval( $atts['limite'] ),
            'orderby'   => 'fecha_inicio',
            'order'     => 'DESC',
        );
        // Mostramos abiertos y cerrados (no borradores)
        $eventos_abiertos  = WPER_DB::get_eventos( array_merge( $args, array( 'estado' => 'abierto' ) ) );
        $eventos_cerrados  = WPER_DB::get_eventos( array_merge( $args, array( 'estado' => 'cerrado' ) ) );
        $eventos = array_merge( $eventos_abiertos, $eventos_cerrados );

        // Filtrar por provincia si se indica
        if ( ! empty( $atts['provincia'] ) ) {
            $prov = sanitize_text_field( $atts['provincia'] );
            $eventos = array_filter( $eventos, function($e) use ($prov) {
                return strtolower( $e->provincia ) === strtolower( $prov );
            });
        }

        // Ordenar por fecha_inicio DESC
        usort( $eventos, function($a, $b) {
            return strtotime( $b->fecha_inicio ) - strtotime( $a->fecha_inicio );
        });

        ob_start();
        include WPER_PLUGIN_DIR . 'public/views/calendario.php';
        return ob_get_clean();
    }

    // ══════════════════════════════════════════════════════
    //  [wper_inscripcion id="X"]
    // ══════════════════════════════════════════════════════
    public function shortcode_inscripcion( $atts ) {
        $atts = shortcode_atts( array( 'id' => 0 ), $atts, 'wper_inscripcion' );
        $evento_id = intval( $atts['id'] );

        if ( ! $evento_id ) {
            return '<p class="dp-error">' . __( 'ID de evento no especificado.', 'wp-events-registration' ) . '</p>';
        }

        $evento = WPER_DB::get_evento( $evento_id );
        if ( ! $evento || $evento->estado === 'borrador' ) {
            return '';
        }

        $mensaje = '';
        $error   = '';

        ob_start();
        include WPER_PLUGIN_DIR . 'public/views/inscripcion-form.php';
        return ob_get_clean();
    }

    // ══════════════════════════════════════════════════════
    //  [wper_ficha id="X"]
    // ══════════════════════════════════════════════════════
    public function shortcode_ficha( $atts ) {
        $atts = shortcode_atts( array( 'id' => 0 ), $atts, 'wper_ficha' );
        $evento_id = intval( $atts['id'] );

        if ( ! $evento_id ) return '';

        $evento = WPER_DB::get_evento( $evento_id );
        if ( ! $evento || $evento->estado === 'borrador' ) return '';

        ob_start();
        include WPER_PLUGIN_DIR . 'public/views/evento-detalle.php';
        return ob_get_clean();
    }

    // ══════════════════════════════════════════════════════
    //  AJAX: procesar inscripción
    // ══════════════════════════════════════════════════════
    public function ajax_inscribir() {
        // Verificar nonce
        if ( ! check_ajax_referer( 'wper_inscribir_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Petición no válida.', 'wp-events-registration' ) ) );
        }

        $evento_id = intval( $_POST['evento_id'] ?? 0 );
        $evento    = WPER_DB::get_evento( $evento_id );

        if ( ! $evento ) {
            wp_send_json_error( array( 'message' => __( 'Evento no encontrado.', 'wp-events-registration' ) ) );
        }

        if ( $evento->estado !== 'abierto' ) {
            wp_send_json_error( array( 'message' => __( 'Las inscripciones están cerradas.', 'wp-events-registration' ) ) );
        }

        // Validar campos obligatorios
        $nombre    = sanitize_text_field( $_POST['nombre']    ?? '' );
        $apellidos = sanitize_text_field( $_POST['apellidos'] ?? '' );

        if ( empty( $nombre ) || empty( $apellidos ) ) {
            wp_send_json_error( array( 'message' => __( 'Nombre y apellidos son obligatorios.', 'wp-events-registration' ) ) );
        }

        $email = sanitize_email( $_POST['email'] ?? '' );

        // Verificar duplicado por email
        if ( $email && WPER_DB::existe_inscripcion( $evento_id, $email ) ) {
            wp_send_json_error( array( 'message' => __( 'Ya existe una inscripción con ese email para este evento.', 'wp-events-registration' ) ) );
        }

        $id = WPER_DB::insert_inscripcion( array(
            'evento_id'   => $evento_id,
            'nombre'      => $nombre,
            'apellidos'   => $apellidos,
            'fide_id'     => sanitize_text_field( $_POST['fide_id']     ?? '' ),
            'telefono'    => sanitize_text_field( $_POST['telefono']    ?? '' ),
            'email'       => $email,
            'alojamiento' => isset( $_POST['alojamiento'] ) ? 1 : 0,
            'observaciones' => sanitize_textarea_field( $_POST['observaciones'] ?? '' ),
        ) );

        if ( ! $id ) {
            wp_send_json_error( array( 'message' => __( 'Error al guardar la inscripción.', 'wp-events-registration' ) ) );
        }

        // Enviar emails
        $this->enviar_emails( $evento, $nombre, $apellidos, $email );

        wp_send_json_success( array(
            'message' => __( '¡Inscripción completada! Recibirás un email de confirmación.', 'wp-events-registration' ),
        ) );
    }

    private function enviar_emails( $evento, $nombre, $apellidos, $email ) {
        $notificar = get_option( 'wper_email_notificar', '1' );
        $admin_email = get_option( 'wper_email_admin', get_option('admin_email') );
        $nombre_evento = $evento->nombre;
        $site_name   = get_bloginfo('name');
        
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        $headers[] = 'From: ' . $site_name . ' <' . $admin_email . '>';

        // Email al inscrito
        if ( $email ) {
            $asunto  = sprintf( __( 'Confirmación de inscripción: %s', 'wp-events-registration' ), $nombre_evento );
            $cuerpo  = sprintf( __( "Hola %s %s,\n\nTu inscripción al evento \"%s\" ha sido registrada correctamente.\n\nFecha del evento: %s — %s\nLugar: %s, %s\n\nUn saludo.", 'wp-events-registration' ),
                $nombre, $apellidos, $nombre_evento,
                date_i18n( 'd/m/Y', strtotime( $evento->fecha_inicio ) ),
                date_i18n( 'd/m/Y', strtotime( $evento->fecha_fin ) ),
                $evento->poblacion, $evento->provincia
            );
            wp_mail( $email, $asunto, $cuerpo, $headers );
        }

        // Notificación al admin
        if ( $notificar ) {
            $asunto_admin = sprintf( __( 'Nueva inscripción en %s', 'wp-events-registration' ), $nombre_evento );
            $cuerpo_admin = sprintf( __( "Nueva inscripción recibida:\n\nEvento: %s\nNombre: %s %s\nEmail: %s\n\nAccede al panel para verla: %s", 'wp-events-registration' ),
                $nombre_evento, $nombre, $apellidos, $email,
                admin_url( 'admin.php?page=wper-inscripciones&evento_id=' . $evento->id )
            );
            wp_mail( $admin_email, $asunto_admin, $cuerpo_admin, $headers );
        }
    }
}
