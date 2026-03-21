<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class DP_Torneos_Admin {

    public function init() {
        add_action( 'admin_menu',            array( $this, 'register_menus' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'admin_post_dp_save_evento',       array( $this, 'handle_save_evento' ) );
        add_action( 'admin_post_dp_delete_evento',     array( $this, 'handle_delete_evento' ) );
        add_action( 'admin_post_dp_delete_inscripcion',array( $this, 'handle_delete_inscripcion' ) );
        add_action( 'admin_post_dp_export_pdf',        array( $this, 'handle_export_pdf' ) );
        add_action( 'admin_post_dp_export_csv',        array( $this, 'handle_export_csv' ) );
        add_action( 'admin_post_dp_save_ajustes',      array( $this, 'handle_save_ajustes' ) );
    }

    // ── Menús ────────────────────────────────────────────
    public function register_menus() {
        add_menu_page(
            __( 'Torneos Ajedrez', 'dp-torneos' ),
            __( 'Torneos', 'dp-torneos' ),
            'manage_options',
            'dp-torneos',
            array( $this, 'page_dashboard' ),
            'dashicons-awards',
            30
        );
        add_submenu_page( 'dp-torneos', __( 'Dashboard', 'dp-torneos' ),       __( 'Dashboard', 'dp-torneos' ),       'manage_options', 'dp-torneos',                array( $this, 'page_dashboard' ) );
        add_submenu_page( 'dp-torneos', __( 'Eventos', 'dp-torneos' ),         __( 'Eventos', 'dp-torneos' ),         'manage_options', 'dp-torneos-eventos',        array( $this, 'page_eventos' ) );
        add_submenu_page( 'dp-torneos', __( 'Nuevo Evento', 'dp-torneos' ),    __( 'Nuevo Evento', 'dp-torneos' ),    'manage_options', 'dp-torneos-nuevo',          array( $this, 'page_evento_form' ) );
        add_submenu_page( 'dp-torneos', __( 'Inscripciones', 'dp-torneos' ),   __( 'Inscripciones', 'dp-torneos' ),   'manage_options', 'dp-torneos-inscripciones',  array( $this, 'page_inscripciones' ) );
        add_submenu_page( 'dp-torneos', __( 'Ajustes', 'dp-torneos' ),         __( 'Ajustes', 'dp-torneos' ),         'manage_options', 'dp-torneos-ajustes',        array( $this, 'page_ajustes' ) );
    }

    public function enqueue_assets( $hook ) {
        if ( strpos( $hook, 'dp-torneos' ) === false ) return;
        wp_enqueue_style(  'dp-torneos-admin', DP_TORNEOS_PLUGIN_URL . 'admin/assets/admin.css', array(), DP_TORNEOS_VERSION );
        wp_enqueue_script( 'dp-torneos-admin', DP_TORNEOS_PLUGIN_URL . 'admin/assets/admin.js',  array('jquery'), DP_TORNEOS_VERSION, true );
    }

    // ── Páginas ──────────────────────────────────────────
    public function page_dashboard() {
        $stats = DP_Torneos_DB::get_stats();
        $ultimas_inscripciones = DP_Torneos_DB::get_todas_inscripciones( 10, 0 );
        $eventos_abiertos = DP_Torneos_DB::get_eventos( array( 'estado' => 'abierto', 'limite' => 5 ) );
        include DP_TORNEOS_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    public function page_eventos() {
        $estado_filtro = sanitize_text_field( $_GET['estado'] ?? '' );
        $paged  = max( 1, intval( $_GET['paged'] ?? 1 ) );
        $limite = 20;
        $offset = ( $paged - 1 ) * $limite;

        $args = array( 'limite' => $limite, 'offset' => $offset, 'orderby' => 'fecha_inicio', 'order' => 'DESC' );
        if ( $estado_filtro ) $args['estado'] = $estado_filtro;

        $eventos = DP_Torneos_DB::get_eventos( $args );
        $total   = DP_Torneos_DB::count_eventos( $estado_filtro ? array('estado' => $estado_filtro) : array() );
        $total_pages = ceil( $total / $limite );

        $mensaje = sanitize_text_field( $_GET['msg'] ?? '' );
        include DP_TORNEOS_PLUGIN_DIR . 'admin/views/eventos-list.php';
    }

    public function page_evento_form() {
        $evento_id = intval( $_GET['id'] ?? 0 );
        $evento    = $evento_id ? DP_Torneos_DB::get_evento( $evento_id ) : null;
        $error     = sanitize_text_field( $_GET['error'] ?? '' );
        include DP_TORNEOS_PLUGIN_DIR . 'admin/views/evento-form.php';
    }

    public function page_inscripciones() {
        $evento_id = intval( $_GET['evento_id'] ?? 0 );
        $paged  = max( 1, intval( $_GET['paged'] ?? 1 ) );
        $limite = 30;
        $offset = ( $paged - 1 ) * $limite;

        if ( $evento_id ) {
            $evento        = DP_Torneos_DB::get_evento( $evento_id );
            $inscripciones = DP_Torneos_DB::get_inscripciones( $evento_id );
            $total         = count( $inscripciones );
        } else {
            $evento        = null;
            $inscripciones = DP_Torneos_DB::get_todas_inscripciones( $limite, $offset );
            $total         = DP_Torneos_DB::count_inscripciones();
        }

        $eventos_lista = DP_Torneos_DB::get_eventos( array( 'limite' => 200, 'orderby' => 'nombre', 'order' => 'ASC' ) );
        $mensaje = sanitize_text_field( $_GET['msg'] ?? '' );
        include DP_TORNEOS_PLUGIN_DIR . 'admin/views/inscripciones-list.php';
    }

    public function page_ajustes() {
        $saved = isset( $_GET['saved'] );
        include DP_TORNEOS_PLUGIN_DIR . 'admin/views/ajustes.php';
    }

    // ── Handlers POST ────────────────────────────────────

    public function handle_save_evento() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Sin permisos.' );
        check_admin_referer( 'dp_save_evento' );

        $evento_id = intval( $_POST['evento_id'] ?? 0 );

        // Validación básica
        $required = array( 'nombre', 'modalidad', 'poblacion', 'provincia', 'fecha_inicio', 'fecha_fin', 'fecha_inicio_inscripcion', 'fecha_fin_inscripcion' );
        foreach ( $required as $field ) {
            if ( empty( $_POST[ $field ] ) ) {
                $url = admin_url( 'admin.php?page=dp-torneos-' . ($evento_id ? 'nuevo&id='.$evento_id : 'nuevo') . '&error=campos_obligatorios' );
                wp_redirect( $url ); exit;
            }
        }

        $data = array(
            'nombre'                   => sanitize_text_field( $_POST['nombre'] ),
            'modalidad'                => in_array( $_POST['modalidad'], array('Individual','Por Equipos') ) ? $_POST['modalidad'] : 'Individual',
            'cuota_inscripcion'        => $_POST['cuota_inscripcion'] !== '' ? floatval( $_POST['cuota_inscripcion'] ) : null,
            'numero_rondas'            => $_POST['numero_rondas'] !== '' ? intval( $_POST['numero_rondas'] ) : null,
            'poblacion'                => sanitize_text_field( $_POST['poblacion'] ),
            'provincia'                => sanitize_text_field( $_POST['provincia'] ),
            'fecha_inicio'             => sanitize_text_field( $_POST['fecha_inicio'] ),
            'fecha_fin'                => sanitize_text_field( $_POST['fecha_fin'] ),
            'fecha_inicio_inscripcion' => sanitize_text_field( $_POST['fecha_inicio_inscripcion'] ),
            'fecha_fin_inscripcion'    => sanitize_text_field( $_POST['fecha_fin_inscripcion'] ),
            'estado'                   => in_array( $_POST['estado'], array('borrador','abierto','cerrado') ) ? $_POST['estado'] : 'borrador',
            'url_bases'                => esc_url_raw( $_POST['url_bases'] ?? '' ),
            'google_maps'              => esc_url_raw( $_POST['google_maps'] ?? '' ),
        );

        if ( $evento_id ) {
            DP_Torneos_DB::update_evento( $evento_id, $data );
            $msg = 'actualizado';
        } else {
            DP_Torneos_DB::insert_evento( $data );
            $msg = 'creado';
        }

        wp_redirect( admin_url( 'admin.php?page=dp-torneos-eventos&msg=' . $msg ) );
        exit;
    }

    public function handle_delete_evento() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Sin permisos.' );
        $id = intval( $_GET['id'] ?? 0 );
        check_admin_referer( 'dp_delete_evento_' . $id );
        DP_Torneos_DB::delete_evento( $id );
        wp_redirect( admin_url( 'admin.php?page=dp-torneos-eventos&msg=eliminado' ) );
        exit;
    }

    public function handle_delete_inscripcion() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Sin permisos.' );
        $id        = intval( $_GET['id'] ?? 0 );
        $evento_id = intval( $_GET['evento_id'] ?? 0 );
        check_admin_referer( 'dp_delete_inscripcion_' . $id );
        DP_Torneos_DB::delete_inscripcion( $id );
        $redirect = admin_url( 'admin.php?page=dp-torneos-inscripciones&msg=ins_eliminada' );
        if ( $evento_id ) $redirect .= '&evento_id=' . $evento_id;
        wp_redirect( $redirect );
        exit;
    }

    public function handle_export_pdf() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Sin permisos.' );
        $evento_id = intval( $_GET['evento_id'] ?? 0 );
        check_admin_referer( 'dp_export_pdf_' . $evento_id );
        DP_Torneos_PDF::generate_pdf( $evento_id );
    }

    public function handle_export_csv() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Sin permisos.' );
        $evento_id = intval( $_GET['evento_id'] ?? 0 );
        check_admin_referer( 'dp_export_csv_' . $evento_id );

        $evento        = DP_Torneos_DB::get_evento( $evento_id );
        $inscripciones = DP_Torneos_DB::get_inscripciones( $evento_id );

        if ( ! $evento ) wp_die( 'Evento no encontrado.' );

        $filename = 'torneo_' . sanitize_title( $evento->nombre ) . '_inscritos.csv';
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

        $out = fopen( 'php://output', 'w' );
        fprintf( $out, chr(0xEF).chr(0xBB).chr(0xBF) ); // BOM UTF-8
        fputcsv( $out, array( 'ID', 'Nombre', 'Apellidos', 'FIDE ID', 'Teléfono', 'Email', 'Alojamiento', 'Fecha inscripción' ), ';' );

        foreach ( $inscripciones as $ins ) {
            fputcsv( $out, array(
                $ins->id,
                $ins->nombre,
                $ins->apellidos,
                $ins->fide_id ?: '',
                $ins->telefono ?: '',
                $ins->email ?: '',
                $ins->alojamiento ? 'Sí' : 'No',
                date_i18n( 'd/m/Y H:i', strtotime( $ins->created_at ) ),
            ), ';' );
        }
        fclose( $out );
        exit;
    }

    public function handle_save_ajustes() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Sin permisos.' );
        check_admin_referer( 'dp_save_ajustes' );

        update_option( 'dp_torneos_email_admin',    sanitize_email( $_POST['email_admin'] ?? '' ) );
        update_option( 'dp_torneos_email_notificar', isset( $_POST['email_notificar'] ) ? '1' : '0' );
        update_option( 'dp_torneos_moneda',          sanitize_text_field( $_POST['moneda'] ?? 'EUR' ) );

        wp_redirect( admin_url( 'admin.php?page=dp-torneos-ajustes&saved=1' ) );
        exit;
    }
}
