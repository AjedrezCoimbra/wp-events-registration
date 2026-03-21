<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class DP_Torneos_Shortcodes {

    public function init() {
        add_shortcode( 'dp_torneo_calendario',   array( $this, 'shortcode_calendario' ) );
        add_shortcode( 'dp_torneo_inscripcion',  array( $this, 'shortcode_inscripcion' ) );
        add_shortcode( 'dp_torneo_ficha',        array( $this, 'shortcode_ficha' ) );

        // AJAX handlers (usuarios no logueados también pueden inscribirse)
        add_action( 'wp_ajax_dp_inscribir',        array( $this, 'ajax_inscribir' ) );
        add_action( 'wp_ajax_nopriv_dp_inscribir', array( $this, 'ajax_inscribir' ) );
    }

    // ══════════════════════════════════════════════════════
    //  [dp_torneo_calendario]
    //  Atributos: provincia, limite
    // ══════════════════════════════════════════════════════
    public function shortcode_calendario( $atts ) {
        $atts = shortcode_atts( array(
            'provincia' => '',
            'limite'    => 20,
        ), $atts, 'dp_torneo_calendario' );

        $args = array(
            'limite'    => intval( $atts['limite'] ),
            'orderby'   => 'fecha_inicio',
            'order'     => 'DESC',
        );
        // Mostramos abiertos y cerrados (no borradores)
        $eventos_abiertos  = DP_Torneos_DB::get_eventos( array_merge( $args, array( 'estado' => 'abierto' ) ) );
        $eventos_cerrados  = DP_Torneos_DB::get_eventos( array_merge( $args, array( 'estado' => 'cerrado' ) ) );
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
        include DP_TORNEOS_PLUGIN_DIR . 'public/views/calendario.php';
        return ob_get_clean();
    }

    // ══════════════════════════════════════════════════════
    //  [dp_torneo_inscripcion id="X"]
    // ══════════════════════════════════════════════════════
    public function shortcode_inscripcion( $atts ) {
        $atts = shortcode_atts( array( 'id' => 0 ), $atts, 'dp_torneo_inscripcion' );
        $evento_id = intval( $atts['id'] );

        if ( ! $evento_id ) {
            return '<p class="dp-error">' . __( 'ID de evento no especificado.', 'dp-torneos' ) . '</p>';
        }

        $evento = DP_Torneos_DB::get_evento( $evento_id );
        if ( ! $evento || $evento->estado === 'borrador' ) {
            return '';
        }

        $mensaje = '';
        $error   = '';

        ob_start();
        include DP_TORNEOS_PLUGIN_DIR . 'public/views/inscripcion-form.php';
        return ob_get_clean();
    }

    // ══════════════════════════════════════════════════════
    //  [dp_torneo_ficha id="X"]
    // ══════════════════════════════════════════════════════
    public function shortcode_ficha( $atts ) {
        $atts = shortcode_atts( array( 'id' => 0 ), $atts, 'dp_torneo_ficha' );
        $evento_id = intval( $atts['id'] );

        if ( ! $evento_id ) return '';

        $evento = DP_Torneos_DB::get_evento( $evento_id );
        if ( ! $evento || $evento->estado === 'borrador' ) return '';

        ob_start();
        include DP_TORNEOS_PLUGIN_DIR . 'public/views/evento-detalle.php';
        return ob_get_clean();
    }

    // ══════════════════════════════════════════════════════
    //  AJAX: procesar inscripción
    // ══════════════════════════════════════════════════════
    public function ajax_inscribir() {
        // Verificar nonce
        if ( ! check_ajax_referer( 'dp_inscribir_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Petición no válida.', 'dp-torneos' ) ) );
        }

        $evento_id = intval( $_POST['evento_id'] ?? 0 );
        $evento    = DP_Torneos_DB::get_evento( $evento_id );

        if ( ! $evento ) {
            wp_send_json_error( array( 'message' => __( 'Evento no encontrado.', 'dp-torneos' ) ) );
        }

        if ( $evento->estado !== 'abierto' ) {
            wp_send_json_error( array( 'message' => __( 'Las inscripciones están cerradas.', 'dp-torneos' ) ) );
        }

        // Validar campos obligatorios
        $nombre    = sanitize_text_field( $_POST['nombre']    ?? '' );
        $apellidos = sanitize_text_field( $_POST['apellidos'] ?? '' );

        if ( empty( $nombre ) || empty( $apellidos ) ) {
            wp_send_json_error( array( 'message' => __( 'Nombre y apellidos son obligatorios.', 'dp-torneos' ) ) );
        }

        $email = sanitize_email( $_POST['email'] ?? '' );

        // Verificar duplicado por email
        if ( $email && DP_Torneos_DB::existe_inscripcion( $evento_id, $email ) ) {
            wp_send_json_error( array( 'message' => __( 'Ya existe una inscripción con ese email para este evento.', 'dp-torneos' ) ) );
        }

        $id = DP_Torneos_DB::insert_inscripcion( array(
            'evento_id'   => $evento_id,
            'nombre'      => $nombre,
            'apellidos'   => $apellidos,
            'fide_id'     => sanitize_text_field( $_POST['fide_id']     ?? '' ),
            'telefono'    => sanitize_text_field( $_POST['telefono']    ?? '' ),
            'email'       => $email,
            'alojamiento' => isset( $_POST['alojamiento'] ) ? 1 : 0,
        ) );

        if ( ! $id ) {
            wp_send_json_error( array( 'message' => __( 'Error al guardar la inscripción.', 'dp-torneos' ) ) );
        }

        // Enviar emails
        $this->enviar_emails( $evento, $nombre, $apellidos, $email );

        wp_send_json_success( array(
            'message' => __( '¡Inscripción completada! Recibirás un email de confirmación.', 'dp-torneos' ),
        ) );
    }

    private function enviar_emails( $evento, $nombre, $apellidos, $email ) {
        $notificar = get_option( 'dp_torneos_email_notificar', '1' );
        $admin_email = get_option( 'dp_torneos_email_admin', get_option('admin_email') );
        $nombre_torneo = $evento->nombre;

        // Email al inscrito
        if ( $email ) {
            $asunto  = sprintf( __( 'Confirmación de inscripción: %s', 'dp-torneos' ), $nombre_torneo );
            $cuerpo  = sprintf( __( "Hola %s %s,\n\nTu inscripción al torneo \"%s\" ha sido registrada correctamente.\n\nFecha del torneo: %s — %s\nLugar: %s, %s\n\nUn saludo.", 'dp-torneos' ),
                $nombre, $apellidos, $nombre_torneo,
                date_i18n( 'd/m/Y', strtotime( $evento->fecha_inicio ) ),
                date_i18n( 'd/m/Y', strtotime( $evento->fecha_fin ) ),
                $evento->poblacion, $evento->provincia
            );
            wp_mail( $email, $asunto, $cuerpo );
        }

        // Notificación al admin
        if ( $notificar ) {
            $asunto_admin = sprintf( __( 'Nueva inscripción en %s', 'dp-torneos' ), $nombre_torneo );
            $cuerpo_admin = sprintf( __( "Nueva inscripción recibida:\n\nTorneo: %s\nNombre: %s %s\nEmail: %s\n\nAccede al panel para verla: %s", 'dp-torneos' ),
                $nombre_torneo, $nombre, $apellidos, $email,
                admin_url( 'admin.php?page=dp-torneos-inscripciones&evento_id=' . $evento->id )
            );
            wp_mail( $admin_email, $asunto_admin, $cuerpo_admin );
        }
    }
}
