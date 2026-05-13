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
        $atts = shortcode_atts( array(), $atts, 'wper_calendario' );
        
        global $wpdb;
        $t = WPER_DB::tabla_eventos();
        $hoy = current_time( 'Y-m-d' );

        // 1. EVENTOS ABIERTOS (Inscripción abierta)
        // estado = 'abierto' AND hoy <= fecha_fin_inscripcion
        $eventos_abiertos = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$t} WHERE estado = 'abierto' AND fecha_fin_inscripcion >= %s ORDER BY fecha_inicio ASC",
            $hoy
        ) );

        // Paginación para las otras pestañas
        $paged_cerrados    = max( 1, intval( $_GET['wper_paged_c'] ?? 1 ) );
        $paged_finalizados = max( 1, intval( $_GET['wper_paged_f'] ?? 1 ) );
        $limite = 12;

        // 2. EVENTOS CERRADOS (Inscripción cerrada pero torneo NO finalizado)
        // (estado = 'cerrado' OR (estado = 'abierto' AND hoy > fecha_fin_inscripcion)) AND hoy <= fecha_fin
        $offset_c = ( $paged_cerrados - 1 ) * $limite;
        $eventos_cerrados = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$t} 
             WHERE (estado = 'cerrado' OR (estado = 'abierto' AND fecha_fin_inscripcion < %s)) 
             AND fecha_fin >= %s 
             ORDER BY fecha_inicio ASC 
             LIMIT %d OFFSET %d",
            $hoy, $hoy, $limite, $offset_c
        ) );
        $total_cerrados = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$t} WHERE (estado = 'cerrado' OR (estado = 'abierto' AND fecha_fin_inscripcion < %s)) AND fecha_fin >= %s",
            $hoy, $hoy
        ) );
        $total_pages_cerrados = ceil( $total_cerrados / $limite );

        // 3. EVENTOS FINALIZADOS (Torneo terminado)
        // hoy > fecha_fin AND estado != 'borrador'
        $offset_f = ( $paged_finalizados - 1 ) * $limite;
        $eventos_finalizados = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$t} 
             WHERE fecha_fin < %s AND estado != 'borrador' 
             ORDER BY fecha_fin DESC 
             LIMIT %d OFFSET %d",
            $hoy, $limite, $offset_f
        ) );
        $total_finalizados = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$t} WHERE fecha_fin < %s AND estado != 'borrador'",
            $hoy
        ) );
        $total_pages_finalizados = ceil( $total_finalizados / $limite );

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

        if ( $evento->estado !== 'abierto' || strtotime($evento->fecha_fin_inscripcion) < strtotime(current_time('Y-m-d')) ) {
            wp_send_json_error( array( 'message' => __( 'Las inscripciones están cerradas.', 'wp-events-registration' ) ) );
        }

        // Validar campos obligatorios
        $nombre    = sanitize_text_field( $_POST['nombre']    ?? '' );
        $apellidos = sanitize_text_field( $_POST['apellidos'] ?? '' );

        if ( empty( $nombre ) || empty( $apellidos ) ) {
            wp_send_json_error( array( 'message' => __( 'Nombre y apellidos son obligatorios.', 'wp-events-registration' ) ) );
        }

        $email = sanitize_email( $_POST['email'] ?? '' );

        if ( empty( $email ) || ! is_email( $email ) ) {
            wp_send_json_error( array( 'message' => __( 'El email es obligatorio y debe ser válido.', 'wp-events-registration' ) ) );
        }

        $inscripcion_data = array(
            'evento_id'   => $evento_id,
            'nombre'      => $nombre,
            'apellidos'   => $apellidos,
            'fide_id'     => sanitize_text_field( $_POST['fide_id']     ?? '' ),
            'telefono'    => sanitize_text_field( $_POST['telefono']    ?? '' ),
            'email'       => $email,
            'alojamiento' => ! empty( $_POST['alojamiento'] ) ? 1 : 0,
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
            'evento_modalidad'      => $evento->modalidad,
            'evento_lugar'          => $evento->poblacion . ', ' . $evento->provincia,
            'evento_rondas'         => $evento->numero_rondas ? (string) $evento->numero_rondas : '',
            'evento_cuota'          => $evento->cuota_inscripcion
                                           ? number_format( $evento->cuota_inscripcion, 2 ) . ' €'
                                           : __('Gratuito','wp-events-registration'),
            'evento_ritmo'          => $evento->ritmo_juego     ?: '',
            'evento_tiempo'         => $evento->tiempo_juego    ?: '',
            'evento_url_bases'      => $evento->url_bases       ?: '',
            'evento_url_inscritos'  => $evento->url_inscritos   ?: '',
            'evento_cartel_url'     => $evento->cartel_url      ?: '',
            'evento_estado'         => $evento->estado,
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
        if ( ! check_ajax_referer( 'wper_inscribir_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Petición no válida.', 'wp-events-registration' ) ) );
        }

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
