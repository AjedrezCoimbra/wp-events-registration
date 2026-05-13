<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPER_PDF {

    public static function generate_pdf( $evento_id ) {
        $evento = WPER_DB::get_evento( $evento_id );
        if ( ! $evento ) {
            wp_die( __( 'Evento no encontrado.', 'wp-events-registration' ) );
        }

        $inscripciones = WPER_DB::get_inscripciones( $evento_id );

        header( 'Content-Type: text/html; charset=utf-8' );
        header( 'Cache-Control: private, max-age=0, must-revalidate' );

        self::output_html( $evento, $inscripciones );
        exit;
    }

    private static function output_html( $evento, $inscripciones ) {
        $nombre    = esc_html( $evento->nombre );
        $modalidad = esc_html( $evento->modalidad );
        $estado    = esc_html( ucfirst( $evento->estado ) );
        $lugar     = esc_html( $evento->poblacion . ', ' . $evento->provincia );

        $f_inicio  = date_i18n( 'd/m/Y', strtotime( $evento->fecha_inicio ) );
        $f_fin     = date_i18n( 'd/m/Y', strtotime( $evento->fecha_fin ) );

        $ff_ins    = date_i18n( 'd/m/Y', strtotime( $evento->fecha_fin_inscripcion ) );
        $periodo_ins = sprintf( __( 'Hasta el %s', 'wp-events-registration' ), $ff_ins );

        $rondas    = $evento->numero_rondas ? (int) $evento->numero_rondas : '—';
        $cuota     = $evento->cuota_inscripcion
            ? esc_html( number_format( $evento->cuota_inscripcion, 2 ) . ' €' )
            : __( 'Gratuito', 'wp-events-registration' );
        $ritmo     = $evento->ritmo_juego ? esc_html( $evento->ritmo_juego ) : '';
        $tiempo    = $evento->tiempo_juego ? esc_html( $evento->tiempo_juego ) : '';
        $elo_fide  = $evento->elo_fide ? __( 'Sí', 'wp-events-registration' ) : '';
        $total     = count( $inscripciones );
        $gen_date  = date_i18n( 'd/m/Y H:i' );
        $site_name = esc_html( get_bloginfo( 'name' ) );

        ?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $nombre; ?> — <?php _e( 'Listado de inscritos', 'wp-events-registration' ); ?></title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, sans-serif; font-size: 12px; color: #1a1a1a; background: #fff; padding: 20px; }

  .print-bar {
    background: #2271b1; color: #fff; padding: 10px 16px;
    display: flex; align-items: center; gap: 12px;
    margin-bottom: 20px; border-radius: 4px;
  }
  .print-bar button {
    background: #fff; color: #2271b1; border: none; padding: 6px 14px;
    font-size: 13px; font-weight: 600; cursor: pointer; border-radius: 3px;
  }
  .print-bar span { font-size: 13px; }

  h1 { font-size: 20px; margin-bottom: 4px; }
  h2 { font-size: 13px; font-weight: normal; color: #555; margin-bottom: 16px; }

  .meta-grid {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px 16px;
    border: 1px solid #ddd; border-radius: 4px; padding: 12px;
    margin-bottom: 20px; background: #f9f9f9;
  }
  .meta-item { display: flex; flex-direction: column; }
  .meta-label { font-size: 10px; text-transform: uppercase; color: #777; letter-spacing: .4px; }
  .meta-value { font-size: 12px; font-weight: 600; color: #1a1a1a; }

  .section-title {
    font-size: 13px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .5px; color: #2271b1; border-bottom: 2px solid #2271b1;
    padding-bottom: 4px; margin-bottom: 10px;
  }

  table { width: 100%; border-collapse: collapse; font-size: 11px; }
  thead th {
    background: #2271b1; color: #fff; padding: 6px 8px;
    text-align: left; font-weight: 600;
  }
  tbody tr:nth-child(even) { background: #f5f8fc; }
  tbody td { padding: 5px 8px; border-bottom: 1px solid #e5e5e5; vertical-align: top; }
  tbody tr:last-child td { border-bottom: none; }

  .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: 600; }
  .si  { background: #d1fae5; color: #065f46; }
  .no  { background: #fee2e2; color: #991b1b; }

  .footer { margin-top: 20px; font-size: 10px; color: #999; text-align: right; }

  @media print {
    .print-bar { display: none !important; }
    body { padding: 0; }
    @page { margin: 1.5cm; }
    thead { display: table-header-group; }
    tr { page-break-inside: avoid; }
  }
</style>
</head>
<body>

<div class="print-bar">
  <button onclick="window.print()">🖨 <?php _e( 'Imprimir / Guardar como PDF', 'wp-events-registration' ); ?></button>
  <span><?php echo $site_name; ?> &mdash; <?php echo $nombre; ?></span>
</div>

<h1><?php echo $nombre; ?></h1>
<h2><?php echo $lugar; ?> &middot; <?php echo $modalidad; ?></h2>

<div class="meta-grid">
  <div class="meta-item">
    <span class="meta-label"><?php _e( 'Fechas del evento', 'wp-events-registration' ); ?></span>
    <span class="meta-value"><?php echo $f_inicio === $f_fin ? $f_inicio : $f_inicio . ' — ' . $f_fin; ?></span>
  </div>
  <div class="meta-item">
    <span class="meta-label"><?php _e( 'Periodo de inscripción', 'wp-events-registration' ); ?></span>
    <span class="meta-value"><?php echo $periodo_ins; ?></span>
  </div>
  <div class="meta-item">
    <span class="meta-label"><?php _e( 'Estado', 'wp-events-registration' ); ?></span>
    <span class="meta-value"><?php echo $estado; ?></span>
  </div>
  <?php if ( $rondas !== '—' ) : ?>
  <div class="meta-item">
    <span class="meta-label"><?php _e( 'Rondas', 'wp-events-registration' ); ?></span>
    <span class="meta-value"><?php echo esc_html( $rondas ); ?></span>
  </div>
  <?php endif; ?>
  <div class="meta-item">
    <span class="meta-label"><?php _e( 'Cuota', 'wp-events-registration' ); ?></span>
    <span class="meta-value"><?php echo $cuota; ?></span>
  </div>
  <?php if ( $ritmo ) : ?>
  <div class="meta-item">
    <span class="meta-label"><?php _e( 'Ritmo', 'wp-events-registration' ); ?></span>
    <span class="meta-value"><?php echo $ritmo; ?></span>
  </div>
  <?php endif; ?>
  <?php if ( $tiempo ) : ?>
  <div class="meta-item">
    <span class="meta-label"><?php _e( 'Tiempo de juego', 'wp-events-registration' ); ?></span>
    <span class="meta-value"><?php echo $tiempo; ?></span>
  </div>
  <?php endif; ?>
  <?php if ( $elo_fide ) : ?>
  <div class="meta-item">
    <span class="meta-label"><?php _e( 'ELO FIDE', 'wp-events-registration' ); ?></span>
    <span class="meta-value"><?php echo $elo_fide; ?></span>
  </div>
  <?php endif; ?>
  <div class="meta-item">
    <span class="meta-label"><?php _e( 'Total inscritos', 'wp-events-registration' ); ?></span>
    <span class="meta-value"><?php echo $total; ?></span>
  </div>
</div>

<p class="section-title"><?php _e( 'Listado de inscritos', 'wp-events-registration' ); ?> (<?php echo $total; ?>)</p>

<?php if ( empty( $inscripciones ) ) : ?>
  <p style="color:#888; padding: 16px 0;"><?php _e( 'Sin inscripciones registradas.', 'wp-events-registration' ); ?></p>
<?php else : ?>
<table>
  <thead>
    <tr>
      <th style="width:24px">#</th>
      <th><?php _e( 'Nombre', 'wp-events-registration' ); ?></th>
      <th><?php _e( 'Apellidos', 'wp-events-registration' ); ?></th>
      <th><?php _e( 'FIDE ID', 'wp-events-registration' ); ?></th>
      <th><?php _e( 'Email', 'wp-events-registration' ); ?></th>
      <th><?php _e( 'Teléfono', 'wp-events-registration' ); ?></th>
      <th><?php _e( 'Aloj.', 'wp-events-registration' ); ?></th>
      <th><?php _e( 'Observaciones', 'wp-events-registration' ); ?></th>
      <th><?php _e( 'Inscripción', 'wp-events-registration' ); ?></th>
    </tr>
  </thead>
  <tbody>
    <?php $i = 1; foreach ( $inscripciones as $ins ) : ?>
    <tr>
      <td><?php echo $i++; ?></td>
      <td><?php echo esc_html( $ins->nombre ); ?></td>
      <td><?php echo esc_html( $ins->apellidos ); ?></td>
      <td><?php echo esc_html( $ins->fide_id ?: '—' ); ?></td>
      <td><?php echo esc_html( $ins->email ?: '—' ); ?></td>
      <td><?php echo esc_html( $ins->telefono ?: '—' ); ?></td>
      <td>
        <?php if ( $ins->alojamiento ) : ?>
          <span class="badge si"><?php _e( 'Sí', 'wp-events-registration' ); ?></span>
        <?php else : ?>
          <span class="badge no"><?php _e( 'No', 'wp-events-registration' ); ?></span>
        <?php endif; ?>
      </td>
      <td><?php echo esc_html( $ins->observaciones ?: '' ); ?></td>
      <td style="white-space:nowrap"><?php echo esc_html( date_i18n( 'd/m/Y H:i', strtotime( $ins->created_at ) ) ); ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>

<p class="footer"><?php echo $site_name; ?> &mdash; <?php printf( __( 'Generado el %s', 'wp-events-registration' ), $gen_date ); ?></p>

</body>
</html>
<?php
    }
}
