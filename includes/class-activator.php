<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPER_Activator {

    public static function activate() {
        self::create_tables();
        add_option( 'wper_version', WPER_VERSION );
        add_option( 'wper_email_admin', get_option('admin_email') );
        add_option( 'wper_email_notificar', '1' );
        add_option( 'wper_moneda', 'EUR' );
    }

    private static function create_tables() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();

        $sql_eventos = "CREATE TABLE {$wpdb->prefix}wper_eventos (
            id                        INT UNSIGNED     NOT NULL AUTO_INCREMENT,
            nombre                    VARCHAR(255)     NOT NULL,
            modalidad                 ENUM('Individual','Por Equipos') NOT NULL,
            cuota_inscripcion         DECIMAL(8,2)     NULL COMMENT 'En moneda local. NULL = gratuito',
            numero_rondas             TINYINT UNSIGNED NULL,
            poblacion                 VARCHAR(150)     NOT NULL,
            provincia                 VARCHAR(100)     NOT NULL,
            fecha_inicio              DATE             NOT NULL COMMENT 'Inicio del evento',
            fecha_fin                 DATE             NOT NULL COMMENT 'Fin del evento',
            fecha_inicio_inscripcion  DATE             NOT NULL COMMENT 'Apertura de inscripciones',
            fecha_fin_inscripcion     DATE             NOT NULL COMMENT 'Cierre de inscripciones',
            estado                    ENUM('borrador','abierto','cerrado') NOT NULL DEFAULT 'borrador',
            url_bases                 VARCHAR(500)     NULL,
            google_maps               VARCHAR(500)     NULL COMMENT 'URL de Google Maps',
            cartel_url                VARCHAR(500)     NULL COMMENT 'URL de la imagen del cartel',
            observaciones             TEXT             NULL,
            created_at                DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at                DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_estado             (estado),
            INDEX idx_fecha_inicio       (fecha_inicio),
            INDEX idx_fecha_fin_insc     (fecha_fin_inscripcion),
            INDEX idx_provincia          (provincia)
        ) ENGINE=InnoDB $charset COMMENT='Eventos y registros';";

        $sql_inscripciones = "CREATE TABLE {$wpdb->prefix}wper_inscripciones (
            id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
            evento_id   INT UNSIGNED  NOT NULL,
            nombre      VARCHAR(100)  NOT NULL,
            apellidos   VARCHAR(150)  NOT NULL,
            fide_id     VARCHAR(20)   NULL COMMENT 'ID de identificación, opcional',
            telefono    VARCHAR(30)   NULL,
            email       VARCHAR(255)  NULL,
            alojamiento TINYINT(1)    NOT NULL DEFAULT 0 COMMENT '0=No, 1=Sí',
            observaciones TEXT        NULL,
            created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_evento_id (evento_id),
            INDEX idx_fide_id   (fide_id),
            CONSTRAINT fk_wper_inscripcion_evento
                FOREIGN KEY (evento_id)
                REFERENCES {$wpdb->prefix}wper_eventos(id)
                ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB $charset COMMENT='Inscripciones a eventos';";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql_eventos );
        dbDelta( $sql_inscripciones );
    }
}
