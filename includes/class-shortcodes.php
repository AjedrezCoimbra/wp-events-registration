<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPER_Shortcodes {

    public function init() {
        // $this->register_shortcodes(); // Registro vía hook init, eliminado el doble registro
        
        // También en el gancho init por si acaso el orden de carga de los plugins varía
        add_action( 'init', array( $this, 'register_shortcodes' ) );
        
        add_action( 'wp_ajax_wper_inscribir',        array( $this, 'ajax_inscribir' ) );
        add_action( 'wp_ajax_nopriv_wper_inscribir', array( $this, 'ajax_inscribir' ) );
        add_action( 'wp_ajax_wper_get_inscritos',        array( $this, 'ajax_get_inscritos' ) );
        add_action( 'wp_ajax_nopriv_wper_get_inscritos', array( $this, 'ajax_get_inscritos' ) );
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
        // Mostramos abiertos y cerrados (no borradores) en una sola consulta
        $eventos = WPER_DB::get_eventos( array_merge( $args, array( 'estado' => array( 'abierto', 'cerrado' ) ) ) );

        if ( ! is_array( $eventos ) ) {
            $eventos = array();
        }

        // Filtrar por provincia si se indica
        if ( ! empty( $atts['provincia'] ) && ! empty( $eventos ) ) {
            $prov = sanitize_text_field( $atts['provincia'] );
            $eventos = array_filter( $eventos, function($e) use ($prov) {
                return isset($e->provincia) && strtolower( $e->provincia ) === strtolower( $prov );
            });
        }

        // Ordenar por fecha_inicio ASC (los más próximos primero)
        if ( ! empty( $eventos ) ) {
            usort( $eventos, function($a, $b) {
                $f1 = isset($a->fecha_inicio) ? strtotime( $a->fecha_inicio ) : 0;
                $f2 = isset($b->fecha_inicio) ? strtotime( $b->fecha_inicio ) : 0;
                return $f1 - $f2;
            });
        }

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
            return '<p class="wper-aviso wper-aviso-error">' . __( 'ID de evento no especificado.', 'wp-events-registration' ) . '</p>';
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

        $inscripcion_data = array(
            'evento_id'   => $evento_id,
            'nombre'      => $nombre,
            'apellidos'   => $apellidos,
            'fide_id'     => sanitize_text_field( $_POST['fide_id']     ?? '' ),
            'telefono'    => sanitize_text_field( $_POST['telefono']    ?? '' ),
            'email'       => $email,
            'alojamiento' => isset( $_POST['alojamiento'] ) ? 1 : 0,
            'observaciones' => sanitize_textarea_field( $_POST['observaciones'] ?? '' ),
        );

        $id = WPER_DB::insert_inscripcion($inscripcion_data);

        if ( ! $id ) {
            wp_send_json_error( array( 'message' => __( 'Error al guardar la inscripción.', 'wp-events-registration' ) ) );
        }

        // Enviar emails
        $this->enviar_emails( $evento, $inscripcion_data );

        wp_send_json_success( array(
            'message' => __( '¡Inscripción completada! Recibirás un email de confirmación.', 'wp-events-registration' ),
        ) );
    }

    private function enviar_emails( $evento, $inscripcion_data ) {
        $notificar = get_option( 'wper_email_notificar', '1' );
        $site_name = get_bloginfo('name');
        $admin_email = get_option('wper_email_admin', get_option('admin_email'));

        // Variables para el template
        $vars = array(
            'nombre'               => $inscripcion_data['nombre'],
            'apellidos'            => $inscripcion_data['apellidos'],
            'email'                => $inscripcion_data['email'],
            'fide_id'              => $inscripcion_data['fide_id'] ?? '',
            'telefono'             => $inscripcion_data['telefono'] ?? '',
            'alojamiento'          => $inscripcion_data['alojamiento'] ? __('Sí','wp-events-registration') : __('No','wp-events-registration'),
            'observaciones'        => $inscripcion_data['observaciones'] ?? '',
            'evento_nombre'        => $evento->nombre,
            'evento_fecha_inicio'  => date_i18n( 'd/m/Y', strtotime( $evento->fecha_inicio ) ),
            'evento_fecha_fin'     => date_i18n( 'd/m/Y', strtotime( $evento->fecha_fin ) ),
            'evento_poblacion'     => $evento->poblacion,
            'evento_provincia'     => $evento->provincia,
        );

        // 1. Email al inscrito (Confirmación)
        $email = $inscripcion_data['email'];
        $enviar_user = (int) ($evento->enviar_confirmacion ?? 1);

        if ( $email && $enviar_user ) {
            $asunto  = $this->parse_template( get_option( 'wper_email_confirmacion_asunto' ), $vars );
            $cuerpo  = $this->parse_template( get_option( 'wper_email_confirmacion_cuerpo' ), $vars );
            
            $headers = array('Content-Type: text/html; charset=UTF-8');
            $headers[] = 'From: ' . $site_name . ' <' . $admin_email . '>';
            
            $cc  = get_option( 'wper_email_confirmacion_cc' );
            $bcc = get_option( 'wper_email_confirmacion_bcc' );
            if ( ! empty($cc) )  $headers[] = 'Cc: ' . $cc;
            if ( ! empty($bcc) ) $headers[] = 'Bcc: ' . $bcc;

            wp_mail( $email, $asunto, $cuerpo, $headers );
        }

        // 2. Notificación al admin (Aviso)
        if ( $notificar ) {
            $para_admin   = get_option( 'wper_email_notificacion_para', $admin_email );
            $asunto_admin = $this->parse_template( get_option( 'wper_email_notificacion_asunto' ), $vars );
            $cuerpo_admin = $this->parse_template( get_option( 'wper_email_notificacion_cuerpo' ), $vars );

            $headers_admin = array('Content-Type: text/html; charset=UTF-8');
            $headers_admin[] = 'From: ' . $site_name . ' <' . $admin_email . '>';

            $cc_admin  = get_option( 'wper_email_notificacion_cc' );
            $bcc_admin = get_option( 'wper_email_notificacion_bcc' );
            if ( ! empty($cc_admin) )  $headers_admin[] = 'Cc: ' . $cc_admin;
            if ( ! empty($bcc_admin) ) $headers_admin[] = 'Bcc: ' . $bcc_admin;

            wp_mail( $para_admin, $asunto_admin, $cuerpo_admin, $headers_admin );
        }
    }

    // ══════════════════════════════════════════════════════
    //  AJAX: obtener listado de inscritos
    // ══════════════════════════════════════════════════════
    public function ajax_get_inscritos() {
        $evento_id = intval( $_POST['evento_id'] ?? 0 );
        if ( ! $evento_id ) {
            wp_send_json_error( array( 'message' => __( 'ID de evento no válido.', 'wp-events-registration' ) ) );
        }

        $inscripciones = WPER_DB::get_inscripciones( $evento_id );
        
        if ( empty( $inscripciones ) ) {
            wp_send_json_success( array( 'html' => '<p>' . __( 'No hay inscritos todavía.', 'wp-events-registration' ) . '</p>' ) );
        }

        $html = '<table class="wper-table-listado">';
        $html .= '<thead><tr><th>' . __('Nombre','wp-events-registration') . '</th><th>' . __('Apellidos','wp-events-registration') . '</th><th>' . __('ID FIDE','wp-events-registration') . '</th></tr></thead>';
        $html .= '<tbody>';
        foreach ( $inscripciones as $i ) {
            $html .= '<tr>';
            $html .= '<td>' . esc_html( $i->nombre ) . '</td>';
            $html .= '<td>' . esc_html( $i->apellidos ) . '</td>';
            $html .= '<td>' . esc_html( $i->fide_id ?? '-' ) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        wp_send_json_success( array( 'html' => $html ) );
    }

    private function parse_template( $template, $vars ) {
        foreach ( $vars as $key => $value ) {
            $template = str_replace( '{{' . $key . '}}', $value, $template );
        }
        return $template;
    }
}
