<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPER_DB {

    // ── Tabla names ────────────────────────────────────────
    public static function tabla_eventos() {
        global $wpdb;
        return $wpdb->prefix . 'wper_eventos';
    }

    public static function tabla_inscripciones() {
        global $wpdb;
        return $wpdb->prefix . 'wper_inscripciones';
    }

    // ══════════════════════════════════════════════════════
    //  EVENTOS
    // ══════════════════════════════════════════════════════

    public static function get_eventos( $args = array() ) {
        global $wpdb;
        $t = self::tabla_eventos();

        $defaults = array(
            'estado'    => '',
            'provincia' => '',
            'limite'    => 100,
            'offset'    => 0,
            'orderby'   => 'fecha_inicio',
            'order'     => 'ASC',
        );
        $args = wp_parse_args( $args, $defaults );

        $where = array( '1=1' );
        $values = array();

        if ( ! empty( $args['estado'] ) ) {
            $estados = (array) $args['estado'];
            $placeholders = implode( ', ', array_fill( 0, count($estados), '%s' ) );
            $where[]  = "estado IN ($placeholders)";
            foreach ( $estados as $e ) {
                $values[] = $e;
            }
        }
        if ( ! empty( $args['provincia'] ) ) {
            $where[]  = 'provincia = %s';
            $values[] = $args['provincia'];
        }

        $allowed_order  = array( 'ASC', 'DESC' );
        $allowed_fields = array( 'id', 'nombre', 'fecha_inicio', 'fecha_fin', 'estado', 'created_at' );
        $orderby = in_array( $args['orderby'], $allowed_fields ) ? $args['orderby'] : 'fecha_inicio';
        $order   = in_array( strtoupper($args['order']), $allowed_order ) ? strtoupper($args['order']) : 'ASC';

        $where_sql = implode( ' AND ', $where );
        $limit_sql = $wpdb->prepare( 'LIMIT %d OFFSET %d', intval($args['limite']), intval($args['offset']) );

        if ( ! empty( $values ) ) {
            $sql = $wpdb->prepare(
                "SELECT * FROM {$t} WHERE {$where_sql} ORDER BY {$orderby} {$order} {$limit_sql}",
                $values
            );
        } else {
            $sql = "SELECT * FROM {$t} WHERE {$where_sql} ORDER BY {$orderby} {$order} {$limit_sql}";
        }

        return $wpdb->get_results( $sql );
    }

    public static function count_eventos( $args = array() ) {
        global $wpdb;
        $t = self::tabla_eventos();

        $where  = array( '1=1' );
        $values = array();

        if ( ! empty( $args['estado'] ) ) {
            $estados = (array) $args['estado'];
            $placeholders = implode( ', ', array_fill( 0, count($estados), '%s' ) );
            $where[]  = "estado IN ($placeholders)";
            foreach ( $estados as $e ) {
                $values[] = $e;
            }
        }
        if ( ! empty( $args['provincia'] ) ) {
            $where[]  = 'provincia = %s';
            $values[] = $args['provincia'];
        }

        $where_sql = implode( ' AND ', $where );

        if ( ! empty( $values ) ) {
            return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$t} WHERE {$where_sql}", $values ) );
        }
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t} WHERE {$where_sql}" );
    }

    public static function get_evento( $id ) {
        global $wpdb;
        $t = self::tabla_eventos();
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t} WHERE id = %d", intval($id) ) );
    }

    public static function insert_evento( $data ) {
        global $wpdb;
        $wpdb->insert( self::tabla_eventos(), self::sanitize_evento( $data ) );
        return $wpdb->insert_id;
    }

    public static function update_evento( $id, $data ) {
        global $wpdb;
        return $wpdb->update(
            self::tabla_eventos(),
            self::sanitize_evento( $data ),
            array( 'id' => intval($id) )
        );
    }

    public static function delete_evento( $id ) {
        global $wpdb;
        return $wpdb->delete( self::tabla_eventos(), array( 'id' => intval($id) ) );
    }

    public static function auto_close_eventos() {
        global $wpdb;
        $t = self::tabla_eventos();
        $hoy = current_time( 'Y-m-d' );
        
        // Cerramos eventos cuya fecha de fin de inscripción haya pasado (estrictamente menor que hoy)
        $wpdb->query( $wpdb->prepare(
            "UPDATE {$t} SET estado = 'cerrado' WHERE estado = 'abierto' AND fecha_fin_inscripcion < %s AND fecha_fin_inscripcion != '0000-00-00'",
            $hoy
        ) );
    }

    private static function sanitize_evento( $data ) {
        $clean = array();
        $allowed = array(
            'nombre', 'modalidad', 'cuota_inscripcion', 'numero_rondas',
            'tiempo_juego', 'elo_fide', 'ritmo_juego',
            'poblacion', 'provincia', 'fecha_inicio', 'fecha_fin',
            'fecha_inicio_inscripcion', 'fecha_fin_inscripcion',
            'estado', 'url_bases', 'google_maps', 'cartel_url', 'enviar_confirmacion'
        );
        foreach ( $allowed as $field ) {
            if ( isset( $data[ $field ] ) ) {
                $clean[ $field ] = $data[ $field ] === '' ? null : sanitize_text_field( $data[ $field ] );
            }
        }
        if ( isset( $data['observaciones'] ) ) {
            // Permitimos HTML seguro para el editor enriquecido
            $clean['observaciones'] = wp_kses_post( $data['observaciones'] );
        }
        if ( isset( $data['elo_fide'] ) ) {
            $clean['elo_fide'] = $data['elo_fide'] ? 1 : 0;
        }
        if ( isset( $data['enviar_confirmacion'] ) ) {
            $clean['enviar_confirmacion'] = $data['enviar_confirmacion'] ? 1 : 0;
        }
        if ( isset( $clean['cuota_inscripcion'] ) && $clean['cuota_inscripcion'] !== null ) {
            $clean['cuota_inscripcion'] = floatval( $clean['cuota_inscripcion'] );
        }
        if ( isset( $clean['numero_rondas'] ) && $clean['numero_rondas'] !== null ) {
            $clean['numero_rondas'] = intval( $clean['numero_rondas'] );
        }
        if ( isset( $data['url_bases'] ) ) {
            $clean['url_bases'] = esc_url_raw( $data['url_bases'] );
        }
        if ( isset( $data['google_maps'] ) ) {
            $clean['google_maps'] = esc_url_raw( $data['google_maps'] );
        }
        if ( isset( $data['cartel_url'] ) ) {
            $clean['cartel_url'] = esc_url_raw( $data['cartel_url'] );
        }
        return $clean;
    }

    // ══════════════════════════════════════════════════════
    //  INSCRIPCIONES
    // ══════════════════════════════════════════════════════

    public static function get_inscripciones( $evento_id ) {
        global $wpdb;
        $t = self::tabla_inscripciones();
        return $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM {$t} WHERE evento_id = %d ORDER BY created_at ASC", intval($evento_id) )
        );
    }

    public static function get_todas_inscripciones( $limite = 50, $offset = 0 ) {
        global $wpdb;
        $ti = self::tabla_inscripciones();
        $te = self::tabla_eventos();
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT i.*, e.nombre AS evento_nombre
                 FROM {$ti} i
                 LEFT JOIN {$te} e ON e.id = i.evento_id
                 ORDER BY i.created_at DESC
                 LIMIT %d OFFSET %d",
                intval($limite), intval($offset)
            )
        );
    }

    public static function count_inscripciones( $evento_id = null ) {
        global $wpdb;
        $t = self::tabla_inscripciones();
        if ( $evento_id ) {
            return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$t} WHERE evento_id = %d", intval($evento_id) ) );
        }
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t}" );
    }

    public static function insert_inscripcion( $data ) {
        global $wpdb;
        $clean = array(
            'evento_id'   => intval( $data['evento_id'] ),
            'nombre'      => sanitize_text_field( $data['nombre'] ),
            'apellidos'   => sanitize_text_field( $data['apellidos'] ),
            'fide_id'     => ! empty( $data['fide_id'] )   ? sanitize_text_field( $data['fide_id'] )   : null,
            'telefono'    => ! empty( $data['telefono'] )  ? sanitize_text_field( $data['telefono'] )  : null,
            'email'       => ! empty( $data['email'] )     ? sanitize_email( $data['email'] )          : null,
            'alojamiento' => ! empty( $data['alojamiento'] ) ? 1 : 0,
            'observaciones' => ! empty( $data['observaciones'] ) ? sanitize_textarea_field( $data['observaciones'] ) : null,
        );
        $wpdb->insert( self::tabla_inscripciones(), $clean );
        return $wpdb->insert_id;
    }

    public static function delete_inscripcion( $id ) {
        global $wpdb;
        return $wpdb->delete( self::tabla_inscripciones(), array( 'id' => intval($id) ) );
    }

    public static function existe_inscripcion( $evento_id, $email ) {
        global $wpdb;
        $t = self::tabla_inscripciones();
        return (bool) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$t} WHERE evento_id = %d AND email = %s",
                intval($evento_id), sanitize_email($email)
            )
        );
    }

    // ══════════════════════════════════════════════════════
    //  ESTADÍSTICAS DASHBOARD
    // ══════════════════════════════════════════════════════

    public static function get_stats() {
        global $wpdb;
        $te = self::tabla_eventos();
        $ti = self::tabla_inscripciones();

        return array(
            'total_eventos'        => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$te}" ),
            'eventos_abiertos'     => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$te} WHERE estado = 'abierto'" ),
            'eventos_cerrados'     => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$te} WHERE estado = 'cerrado'" ),
            'eventos_borrador'     => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$te} WHERE estado = 'borrador'" ),
            'total_inscripciones'  => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$ti}" ),
            'inscripciones_hoy'    => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$ti} WHERE DATE(created_at) = CURDATE()" ),
        );
    }
}
