<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPER_Admin {

    public function init() {
        add_action( 'admin_menu',            array( $this, 'register_menus' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'admin_post_wper_save_evento',       array( $this, 'handle_save_evento' ) );
        add_action( 'admin_post_wper_delete_evento',     array( $this, 'handle_delete_evento' ) );
        add_action( 'admin_post_wper_delete_inscripcion',array( $this, 'handle_delete_inscripcion' ) );
        add_action( 'admin_post_wper_export_pdf',        array( $this, 'handle_export_pdf' ) );
        add_action( 'admin_post_wper_export_csv',        array( $this, 'handle_export_csv' ) );
        add_action( 'admin_post_wper_save_ajustes',      array( $this, 'handle_save_ajustes' ) );
        add_action( 'admin_post_wper_force_update_check',array( $this, 'handle_force_update_check' ) );
        add_action( 'admin_post_wper_duplicate_evento',  array( $this, 'handle_duplicate_evento' ) );
    }

    // ── Menús ────────────────────────────────────────────
    public function register_menus() {
        add_menu_page(
            __( 'WP Events Registration', 'wp-events-registration' ),
            __( 'Eventos', 'wp-events-registration' ),
            'edit_pages',
            'wper-plugin',
            array( $this, 'page_dashboard' ),
            'dashicons-awards',
            30
        );
        add_submenu_page( 'wper-plugin', __( 'Dashboard', 'wp-events-registration' ),       __( 'Dashboard', 'wp-events-registration' ),       'edit_pages', 'wper-plugin',                array( $this, 'page_dashboard' ) );
        add_submenu_page( 'wper-plugin', __( 'Todos los Eventos', 'wp-events-registration' ), __( 'Todos los Eventos', 'wp-events-registration' ), 'edit_pages', 'wper-eventos',        array( $this, 'page_eventos' ) );
        add_submenu_page( 'wper-plugin', __( 'Nuevo Evento', 'wp-events-registration' ),    __( 'Nuevo Evento', 'wp-events-registration' ),    'edit_pages', 'wper-nuevo',          array( $this, 'page_evento_form' ) );
        add_submenu_page( 'wper-plugin', __( 'Inscripciones', 'wp-events-registration' ),   __( 'Inscripciones', 'wp-events-registration' ),   'edit_pages', 'wper-inscripciones',  array( $this, 'page_inscripciones' ) );
        add_submenu_page( 'wper-plugin', __( 'Ajustes', 'wp-events-registration' ),         __( 'Ajustes', 'wp-events-registration' ),         'manage_options', 'wper-ajustes',        array( $this, 'page_ajustes' ) );
    }

    public function enqueue_assets( $hook ) {
        if ( strpos( $hook, 'wper' ) === false ) return;
        wp_enqueue_media();
        wp_enqueue_style(  'wper-admin', WPER_PLUGIN_URL . 'admin/assets/admin.css', array(), WPER_VERSION );
        wp_enqueue_script( 'wper-admin', WPER_PLUGIN_URL . 'admin/assets/admin.js',  array('jquery'), WPER_VERSION, true );
        wp_localize_script( 'wper-admin', 'wperAdminData', array(
            'i18n' => array(
                'shortcode_copiado' => __( 'Shortcode copiado: ', 'wp-events-registration' ),
                'copia_shortcode'   => __( 'Copia este shortcode:', 'wp-events-registration' ),
                'seleccionar_img'   => __( 'Seleccionar imagen para el cartel', 'wp-events-registration' ),
                'usar_imagen'       => __( 'Usar esta imagen', 'wp-events-registration' ),
            ),
        ) );
    }

    // ── Páginas ──────────────────────────────────────────
    public function page_dashboard() {
        global $wpdb;
        $te = WPER_DB::tabla_eventos();
        $hoy = current_time('Y-m-d');

        $stats = WPER_DB::get_stats();
        $ultimas_inscripciones = WPER_DB::get_todas_inscripciones( 10, 0 );
        
        $eventos_abiertos = WPER_DB::get_eventos( array(
            'estado'  => 'abierto',
            'limite'  => 5,
            'orderby' => 'fecha_inicio',
            'order'   => 'ASC'
        ));

        include WPER_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    public function page_eventos() {
        $vista = sanitize_text_field( $_GET['vista'] ?? '' );
        $paged = max( 1, intval( $_GET['paged'] ?? 1 ) );
        $limite = 20;
        $offset = ( $paged - 1 ) * $limite;
        
        global $wpdb;
        $t   = WPER_DB::tabla_eventos();
        $hoy = current_time( 'Y-m-d' );
        
        $where = "1=1";
        $params = array();

        if ( $vista === 'borrador' ) {
            $where = "estado = %s";
            $params[] = 'borrador';
        } elseif ( $vista === 'abierto' ) {
            $where = "estado = %s AND fecha_fin_inscripcion >= %s";
            $params[] = 'abierto';
            $params[] = $hoy;
        } elseif ( $vista === 'cerrado' ) {
            $where = "(estado = %s OR (estado = %s AND fecha_fin_inscripcion < %s)) AND fecha_fin >= %s";
            $params[] = 'cerrado';
            $params[] = 'abierto';
            $params[] = $hoy;
            $params[] = $hoy;
        } elseif ( $vista === 'finalizado' ) {
            $where = "fecha_fin < %s AND estado != %s";
            $params[] = $hoy;
            $params[] = 'borrador';
        }
        
        $sql = $wpdb->prepare( "SELECT * FROM {$t} WHERE {$where} ORDER BY fecha_inicio DESC LIMIT %d OFFSET %d", array_merge( $params, array( $limite, $offset ) ) );
        $eventos = $wpdb->get_results( $sql );

        $count_sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$t} WHERE {$where}", $params );
        $total = $wpdb->get_var( $count_sql );
        $total_pages = ceil( $total / $limite );
        
        $mensaje = sanitize_text_field( $_GET['msg'] ?? '' );
        include WPER_PLUGIN_DIR . 'admin/views/eventos-list.php';
    }

    public function page_evento_form() {
        $evento_id = intval( $_GET['id'] ?? 0 );
        $evento    = $evento_id ? WPER_DB::get_evento( $evento_id ) : null;
        $error     = sanitize_text_field( $_GET['error'] ?? '' );
        include WPER_PLUGIN_DIR . 'admin/views/evento-form.php';
    }

    public function page_inscripciones() {
        $evento_id = intval( $_GET['evento_id'] ?? 0 );
        $paged  = max( 1, intval( $_GET['paged'] ?? 1 ) );
        $limite = 30;
        $offset = ( $paged - 1 ) * $limite;

        if ( $evento_id ) {
            $evento        = WPER_DB::get_evento( $evento_id );
            $total         = WPER_DB::count_inscripciones( $evento_id );
            $inscripciones = WPER_DB::get_inscripciones( $evento_id, $limite, $offset );
        } else {
            $evento        = null;
            $total         = WPER_DB::count_inscripciones();
            $inscripciones = WPER_DB::get_todas_inscripciones( $limite, $offset );
        }
        $total_pages = ceil( $total / $limite );

        $eventos_lista = WPER_DB::get_eventos( array( 'limite' => 200, 'orderby' => 'nombre', 'order' => 'ASC' ) );
        $mensaje = sanitize_text_field( $_GET['msg'] ?? '' );
        include WPER_PLUGIN_DIR . 'admin/views/inscripciones-list.php';
    }

    public function page_ajustes() {
        $saved = isset( $_GET['saved'] );
        $checked = isset( $_GET['checked'] );
        include WPER_PLUGIN_DIR . 'admin/views/ajustes.php';
    }

    // ── Handlers POST ────────────────────────────────────

    public function handle_save_evento() {
        if ( ! current_user_can( 'edit_pages' ) ) wp_die( __('Sin permisos.', 'wp-events-registration') );
        check_admin_referer( 'wper_save_evento' );

        $post = wp_unslash( $_POST );

        $evento_id = intval( $post['evento_id'] ?? 0 );

        // Validación básica
        $required = array( 'nombre', 'modalidad', 'poblacion', 'provincia', 'fecha_inicio', 'fecha_fin', 'fecha_fin_inscripcion' );
        foreach ( $required as $field ) {
            if ( empty( $post[ $field ] ) ) {
                $url = admin_url( 'admin.php?page=wper-nuevo' . ($evento_id ? '&id='.$evento_id : '') . '&error=campos_obligatorios' );
                wp_redirect( $url ); exit;
            }
        }

        // Validación de fechas
        $f_inicio = strtotime( $post['fecha_inicio'] );
        $f_fin    = strtotime( $post['fecha_fin'] );
        $f_ins    = strtotime( $post['fecha_fin_inscripcion'] );

        if ( $f_fin < $f_inicio ) {
            $url = admin_url( 'admin.php?page=wper-nuevo' . ($evento_id ? '&id='.$evento_id : '') . '&error=fecha_invalida' );
            wp_redirect( $url ); exit;
        }

        if ( $f_ins > $f_fin ) {
            $url = admin_url( 'admin.php?page=wper-nuevo' . ($evento_id ? '&id='.$evento_id : '') . '&error=fecha_inscripcion_invalida' );
            wp_redirect( $url ); exit;
        }

        $data = array(
            'nombre'                   => sanitize_text_field( $post['nombre'] ),
            'modalidad'                => in_array( $post['modalidad'], array('Individual','Por Equipos') ) ? $post['modalidad'] : 'Individual',
            'cuota_inscripcion'        => $post['cuota_inscripcion'] !== '' ? floatval( $post['cuota_inscripcion'] ) : null,
            'numero_rondas'            => $post['numero_rondas'] !== '' ? intval( $post['numero_rondas'] ) : null,
            'poblacion'                => sanitize_text_field( $post['poblacion'] ),
            'provincia'                => sanitize_text_field( $post['provincia'] ),
            'fecha_inicio'             => sanitize_text_field( $post['fecha_inicio'] ),
            'fecha_fin'                => sanitize_text_field( $post['fecha_fin'] ),
            'fecha_fin_inscripcion'    => sanitize_text_field( $post['fecha_fin_inscripcion'] ),
            'estado'                   => in_array( $post['estado'], array('borrador','abierto','cerrado') ) ? $post['estado'] : 'borrador',
            'url_bases'                => esc_url_raw( $post['url_bases'] ?? '' ),
            'url_inscritos'            => esc_url_raw( $post['url_inscritos'] ?? '' ),
            'google_maps'              => esc_url_raw( $post['google_maps'] ?? '' ),
            'cartel_url'               => esc_url_raw( $post['cartel_url'] ?? '' ),
            'observaciones'            => wp_kses_post( $post['observaciones'] ?? '' ),
            'tiempo_juego'             => sanitize_text_field( $post['tiempo_juego'] ?? '' ),
            'elo_fide'                 => isset( $post['elo_fide'] ) ? 1 : 0,
            'subvencionable'           => isset( $post['subvencionable'] ) ? 1 : 0,
            'ritmo_juego'              => sanitize_text_field( $post['ritmo_juego'] ?? '' ),
            'enviar_confirmacion'      => isset( $post['enviar_confirmacion'] ) ? 1 : 0,
            'permitir_inscripcion_web' => isset( $post['permitir_inscripcion_web'] ) ? 1 : 0,
        );

        if ( $evento_id ) {
            WPER_DB::update_evento( $evento_id, $data );
            $msg = 'actualizado';
        } else {
            WPER_DB::insert_evento( $data );
            $msg = 'creado';
        }

        wp_redirect( admin_url( 'admin.php?page=wper-eventos&msg=' . $msg ) );
        exit;
    }

    public function handle_delete_evento() {
        if ( ! current_user_can( 'edit_pages' ) ) wp_die( __('Sin permisos.', 'wp-events-registration') );
        $id = intval( $_GET['id'] ?? 0 );
        check_admin_referer( 'wper_delete_evento_' . $id );
        WPER_DB::delete_evento( $id );
        wp_redirect( admin_url( 'admin.php?page=wper-eventos&msg=eliminado' ) );
        exit;
    }

    public function handle_delete_inscripcion() {
        if ( ! current_user_can( 'edit_pages' ) ) wp_die( __('Sin permisos.', 'wp-events-registration') );
        $id        = intval( $_GET['id'] ?? 0 );
        $evento_id = intval( $_GET['evento_id'] ?? 0 );
        check_admin_referer( 'wper_delete_inscripcion_' . $id );
        WPER_DB::delete_inscripcion( $id );
        $redirect = admin_url( 'admin.php?page=wper-inscripciones&msg=ins_eliminada' );
        if ( $evento_id ) $redirect .= '&evento_id=' . $evento_id;
        wp_redirect( $redirect );
        exit;
    }

    public function handle_export_pdf() {
        if ( ! current_user_can( 'edit_pages' ) ) wp_die( __('Sin permisos.', 'wp-events-registration') );
        $evento_id = intval( $_GET['evento_id'] ?? 0 );
        check_admin_referer( 'wper_export_pdf_' . $evento_id );
        WPER_PDF::generate_pdf( $evento_id );
    }

    public function handle_export_csv() {
        if ( ! current_user_can( 'edit_pages' ) ) wp_die( __('Sin permisos.', 'wp-events-registration') );
        $evento_id = intval( $_GET['evento_id'] ?? 0 );
        check_admin_referer( 'wper_export_csv_' . $evento_id );

        $evento        = WPER_DB::get_evento( $evento_id );
        $inscripciones = WPER_DB::get_inscripciones( $evento_id );

        if ( ! $evento ) wp_die( __('Evento no encontrado.', 'wp-events-registration') );

        $filename = 'evento_' . sanitize_title( $evento->nombre ) . '_inscritos.csv';
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

        $out = fopen( 'php://output', 'w' );
        fprintf( $out, chr(0xEF).chr(0xBB).chr(0xBF) ); // BOM UTF-8
        fputcsv( $out, array( 'ID', 'Nombre', 'Apellidos', 'FIDE ID', 'Teléfono', 'Email', 'Alojamiento', 'Observaciones', 'Fecha inscripción' ), ';' );

        foreach ( $inscripciones as $ins ) {
            fputcsv( $out, array(
                $ins->id,
                $ins->nombre,
                $ins->apellidos,
                $ins->fide_id ?: '',
                $ins->telefono ?: '',
                $ins->email ?: '',
                $ins->alojamiento ? 'Sí' : 'No',
                $ins->observaciones ?: '',
                date_i18n( 'd/m/Y H:i', strtotime( $ins->created_at ) ),
            ), ';' );
        }
        fclose( $out );
        exit;
    }

    public function handle_save_ajustes() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( __('Sin permisos.', 'wp-events-registration') );
        check_admin_referer( 'wper_save_ajustes' );

        update_option( 'wper_email_admin',    sanitize_email( $_POST['email_admin'] ?? '' ) );
        update_option( 'wper_email_notificar', isset( $_POST['email_notificar'] ) ? '1' : '0' );
        update_option( 'wper_moneda',          sanitize_text_field( $_POST['moneda'] ?? 'EUR' ) );
        update_option( 'wper_github_token',    sanitize_text_field( $_POST['github_token'] ?? '' ) );

        // Validar emails en CC/BCC
        $validate_emails = function( $csv ) {
            if ( empty( $csv ) ) return '';
            $parts = array_map( 'trim', explode( ',', $csv ) );
            $valid = array();
            foreach ( $parts as $email ) {
                if ( is_email( $email ) ) {
                    $valid[] = $email;
                }
            }
            return implode( ', ', $valid );
        };
        // Plantillas de correos
        if ( isset( $_POST['email_confirmacion_asunto'] ) ) {
            update_option( 'wper_email_confirmacion_asunto', sanitize_text_field( $_POST['email_confirmacion_asunto'] ) );
            update_option( 'wper_email_confirmacion_cuerpo', wp_kses_post( $_POST['email_confirmacion_cuerpo'] ) );
            update_option( 'wper_email_confirmacion_cc',     $validate_emails( sanitize_text_field( $_POST['email_confirmacion_cc'] ?? '' ) ) );
            update_option( 'wper_email_confirmacion_bcc',    $validate_emails( sanitize_text_field( $_POST['email_confirmacion_bcc'] ?? '' ) ) );
        }
        if ( isset( $_POST['email_notificacion_asunto'] ) ) {
            update_option( 'wper_email_notificacion_para',   sanitize_text_field( $_POST['email_notificacion_para'] ?? '' ) );
            update_option( 'wper_email_notificacion_asunto', sanitize_text_field( $_POST['email_notificacion_asunto'] ) );
            update_option( 'wper_email_notificacion_cuerpo', wp_kses_post( $_POST['email_notificacion_cuerpo'] ) );
            update_option( 'wper_email_notificacion_cc',     $validate_emails( sanitize_text_field( $_POST['email_notificacion_cc'] ?? '' ) ) );
            update_option( 'wper_email_notificacion_bcc',    $validate_emails( sanitize_text_field( $_POST['email_notificacion_bcc'] ?? '' ) ) );
        }

        wp_redirect( admin_url( 'admin.php?page=wper-ajustes&saved=1' ) );
        exit;
    }

    public function handle_force_update_check() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( __('Sin permisos.', 'wp-events-registration') );
        check_admin_referer( 'wper_force_update' );

        // Eliminamos nuestro caché de GitHub
        delete_transient( 'wper_github_release_cache' );
        // Forzamos a WordPress a buscar actualizaciones de nuevo
        delete_site_transient( 'update_plugins' );

        wp_redirect( admin_url( 'admin.php?page=wper-ajustes&checked=1' ) );
        exit;
    }

    public function handle_duplicate_evento() {
        if ( ! current_user_can( 'edit_pages' ) ) wp_die( __('Sin permisos.', 'wp-events-registration') );
        $id = intval( $_GET['id'] ?? 0 );
        check_admin_referer( 'wper_duplicate_evento_' . $id );

        $original = WPER_DB::get_evento( $id );
        if ( ! $original ) wp_die( __('Evento no encontrado.', 'wp-events-registration') );

        $data = (array) $original;
        unset( $data['id'], $data['created_at'], $data['updated_at'] );
        $data['nombre'] = __( 'Copia de', 'wp-events-registration' ) . ' ' . $data['nombre'];
        $data['estado'] = 'borrador';

        $nuevo_id = WPER_DB::insert_evento( $data );

        wp_redirect( admin_url( 'admin.php?page=wper-nuevo&id=' . $nuevo_id . '&msg=duplicado' ) );
        exit;
    }

}
