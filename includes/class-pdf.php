<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Generación de PDF sin librerías externas.
 * Usa la clase nativa de WordPress para cabeceras HTTP
 * y genera un PDF básico con fpdf embebido (incluido inline).
 *
 * Si prefieres TCPDF, instálala en /includes/lib/tcpdf/ y
 * sustituye la llamada en generate_pdf().
 */
class WPER_PDF {

    public static function generate_pdf( $evento_id ) {
        $evento = WPER_DB::get_evento( $evento_id );
        if ( ! $evento ) {
            wp_die( __( 'Evento no encontrado.', 'wp-events-registration' ) );
        }

        $inscripciones = WPER_DB::get_inscripciones( $evento_id );

        // Cabeceras HTTP para descarga
        $filename = 'evento_' . sanitize_title( $evento->nombre ) . '_inscritos.pdf';
        header( 'Content-Type: application/pdf' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Cache-Control: private, max-age=0, must-revalidate' );

        // ── Construcción manual del PDF (sin librerías) ──
        $pdf = self::build_pdf( $evento, $inscripciones );
        echo $pdf;
        exit;
    }

    private static function build_pdf( $evento, $inscripciones ) {
        $lines = array();

        ob_start();

        $titulo   = wp_strip_all_tags( $evento->nombre );
        $modalidad = wp_strip_all_tags( $evento->modalidad );
        $estado   = ucfirst( wp_strip_all_tags( $evento->estado ) );
        $lugar    = wp_strip_all_tags( $evento->poblacion ) . ', ' . wp_strip_all_tags( $evento->provincia );
        $f_inicio = date_i18n( 'd/m/Y', strtotime( $evento->fecha_inicio ) );
        $f_fin    = date_i18n( 'd/m/Y', strtotime( $evento->fecha_fin ) );
        $fi_ins   = date_i18n( 'd/m/Y', strtotime( $evento->fecha_inicio_inscripcion ) );
        $ff_ins   = date_i18n( 'd/m/Y', strtotime( $evento->fecha_fin_inscripcion ) );
        $rondas   = $evento->numero_rondas ? $evento->numero_rondas : '—';
        $cuota    = $evento->cuota_inscripcion ? number_format( $evento->cuota_inscripcion, 2 ) . ' €' : 'Gratuito';
        $bases    = $evento->url_bases ? $evento->url_bases : '—';
        $total_ins = count( $inscripciones );

        // PDF raw content usando FPDF bundled
        $fpdf_path = WPER_PLUGIN_DIR . 'includes/lib/fpdf/fpdf.php';

        if ( file_exists( $fpdf_path ) ) {
            return self::build_with_fpdf( $fpdf_path, $evento, $inscripciones );
        }

        return self::build_raw_pdf( $titulo, $modalidad, $estado, $lugar,
            $f_inicio, $f_fin, $fi_ins, $ff_ins, $rondas, $cuota, $bases,
            $inscripciones );
    }

    private static function build_raw_pdf(
        $titulo, $modalidad, $estado, $lugar,
        $f_inicio, $f_fin, $fi_ins, $ff_ins,
        $rondas, $cuota, $bases, $inscripciones
    ) {
        // Contenido del PDF como texto enriquecido usando PDF stream básico
        $content  = "%PDF-1.4\n";
        $objects  = array();
        $xref_pos = array();

        // Helper para escapar strings PDF
        $esc = function( $str ) {
            return str_replace( array('\\','(',')',"\n","\r"), array('\\\\','\\(','\\)','\\n','\\r'), $str );
        };

        // Objeto 1: Catálogo
        $objects[1] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";

        // Objeto 2: Pages
        $objects[2] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";

        // Contenido de la página
        $stream_parts = array();
        $stream_parts[] = "BT";
        $stream_parts[] = "/F1 18 Tf";
        $stream_parts[] = "50 780 Td";
        $stream_parts[] = "(" . $esc( mb_convert_encoding( 'EVENTO DE AJEDREZ', 'ISO-8859-1', 'UTF-8' ) ) . ") Tj";
        $stream_parts[] = "/F1 14 Tf";
        $stream_parts[] = "0 -25 Td";
        $stream_parts[] = "(" . $esc( mb_convert_encoding( $titulo, 'ISO-8859-1', 'UTF-8' ) ) . ") Tj";
        $stream_parts[] = "/F1 10 Tf";
        $stream_parts[] = "0 -30 Td";

        $info_lines = array(
            'Modalidad: '   . $modalidad,
            'Estado: '      . $estado,
            'Lugar: '       . $lugar,
            'Evento: '      . $f_inicio . ' — ' . $f_fin,
            'Inscripcion: ' . $fi_ins   . ' — ' . $ff_ins,
            'Rondas: '      . $rondas,
            'Cuota: '       . $cuota,
            'Bases: '       . ( strlen($bases) > 60 ? substr($bases,0,57).'...' : $bases ),
            '',
            'LISTADO DE INSCRITOS (' . count($inscripciones) . ')',
            str_repeat('-', 60),
        );

        foreach ( $info_lines as $line ) {
            $stream_parts[] = "(" . $esc( mb_convert_encoding( $line, 'ISO-8859-1', 'UTF-8' ) ) . ") Tj";
            $stream_parts[] = "0 -14 Td";
        }

        $i = 1;
        foreach ( $inscripciones as $ins ) {
            $line = $i . '. ' .
                mb_convert_encoding( $ins->nombre . ' ' . $ins->apellidos, 'ISO-8859-1', 'UTF-8' ) .
                ' | FIDE: ' . ( $ins->fide_id ?: '—' ) .
                ' | ' . ( $ins->email ?: '—' ) .
                ' | Aloj: ' . ( $ins->alojamiento ? 'Si' : 'No' ) .
                ( $ins->observaciones ? ' | Obs: ' . mb_convert_encoding( $ins->observaciones, 'ISO-8859-1', 'UTF-8' ) : '' );
            
            // Truncar si es muy larga para una sola línea PDF básica
            if ( strlen( $line ) > 100 ) $line = substr( $line, 0, 97 ) . '...';
            
            $stream_parts[] = "(" . $esc( $line ) . ") Tj";
            $stream_parts[] = "0 -13 Td";
            $i++;
        }

        if ( empty( $inscripciones ) ) {
            $stream_parts[] = "(" . $esc( mb_convert_encoding( 'Sin inscripciones registradas.', 'ISO-8859-1', 'UTF-8' ) ) . ") Tj";
            $stream_parts[] = "0 -13 Td";
        }

        $gen_date = date('d/m/Y H:i');
        $stream_parts[] = "0 -20 Td";
        $stream_parts[] = "(" . $esc( mb_convert_encoding( "Generado el $gen_date", 'ISO-8859-1', 'UTF-8' ) ) . ") Tj";
        $stream_parts[] = "ET";

        $stream = implode( "\n", $stream_parts );
        $stream_len = strlen( $stream );

        // Objeto 3: Stream de contenido
        $objects[3] = "3 0 obj\n<< /Length $stream_len >>\nstream\n$stream\nendstream\nendobj\n";

        // Objeto 4: Página
        $objects[4] = "4 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 3 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj\n";

        // Actualizar Pages kids
        $objects[2] = "2 0 obj\n<< /Type /Pages /Kids [4 0 R] /Count 1 >>\nendobj\n";

        // Objeto 5: Fuente
        $objects[5] = "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>\nendobj\n";

        // Ensamblar PDF
        $pdf = "%PDF-1.4\n";
        foreach ( array(1,2,5,3,4) as $n ) {
            $xref_pos[$n] = strlen( $pdf );
            $pdf .= $objects[$n];
        }

        // xref
        $xref_offset = strlen( $pdf );
        $pdf .= "xref\n0 6\n";
        $pdf .= "0000000000 65535 f \n";
        foreach ( array(1,2,3,4,5) as $n ) {
            $pdf .= str_pad( $xref_pos[$n], 10, '0', STR_PAD_LEFT ) . " 00000 n \n";
        }
        $pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\n";
        $pdf .= "startxref\n$xref_offset\n%%EOF\n";

        return $pdf;
    }
}
