<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap dp-torneos-wrap">
  <h1>♟ <?php _e( 'Dirección Deportiva - Eventos | Dashboard', 'dp-torneos' ); ?></h1>

  <div class="dp-stats-grid">
    <div class="dp-stat-card dp-stat-abierto">
      <span class="dp-stat-num"><?php echo $stats['eventos_abiertos']; ?></span>
      <span class="dp-stat-label"><?php _e( 'Eventos abiertos', 'dp-torneos' ); ?></span>
    </div>
    <div class="dp-stat-card dp-stat-cerrado">
      <span class="dp-stat-num"><?php echo $stats['eventos_cerrados']; ?></span>
      <span class="dp-stat-label"><?php _e( 'Eventos cerrados', 'dp-torneos' ); ?></span>
    </div>
    <div class="dp-stat-card dp-stat-borrador">
      <span class="dp-stat-num"><?php echo $stats['eventos_borrador']; ?></span>
      <span class="dp-stat-label"><?php _e( 'Borradores', 'dp-torneos' ); ?></span>
    </div>
    <div class="dp-stat-card dp-stat-inscripciones">
      <span class="dp-stat-num"><?php echo $stats['total_inscripciones']; ?></span>
      <span class="dp-stat-label"><?php _e( 'Total inscripciones', 'dp-torneos' ); ?></span>
    </div>
    <div class="dp-stat-card dp-stat-hoy">
      <span class="dp-stat-num"><?php echo $stats['inscripciones_hoy']; ?></span>
      <span class="dp-stat-label"><?php _e( 'Inscripciones hoy', 'dp-torneos' ); ?></span>
    </div>
  </div>

  <div class="dp-dashboard-cols">

    <div class="dp-dashboard-col">
      <h2><?php _e( 'Accesos rápidos', 'dp-torneos' ); ?></h2>
      <a href="<?php echo admin_url('admin.php?page=dp-torneos-nuevo'); ?>" class="button button-primary dp-btn-lg">
        ➕ <?php _e( 'Crear nuevo evento', 'dp-torneos' ); ?>
      </a>
      <a href="<?php echo admin_url('admin.php?page=dp-torneos-eventos'); ?>" class="button dp-btn-lg">
        📋 <?php _e( 'Ver todos los eventos', 'dp-torneos' ); ?>
      </a>
      <a href="<?php echo admin_url('admin.php?page=dp-torneos-inscripciones'); ?>" class="button dp-btn-lg">
        👥 <?php _e( 'Ver inscripciones', 'dp-torneos' ); ?>
      </a>
    </div>

    <div class="dp-dashboard-col">
      <h2><?php _e( 'Eventos con inscripción abierta', 'dp-torneos' ); ?></h2>
      <?php if ( empty( $eventos_abiertos ) ) : ?>
        <p class="dp-muted"><?php _e( 'No hay eventos abiertos ahora mismo.', 'dp-torneos' ); ?></p>
      <?php else : ?>
        <ul class="dp-list">
          <?php foreach ( $eventos_abiertos as $ev ) : ?>
            <li>
              <strong><?php echo esc_html( $ev->nombre ); ?></strong><br>
              <small><?php echo esc_html( date_i18n('d/m/Y', strtotime($ev->fecha_inicio)) ); ?> · <?php echo esc_html( $ev->poblacion ); ?></small>
              — <a href="<?php echo admin_url('admin.php?page=dp-torneos-inscripciones&evento_id='.$ev->id); ?>"><?php _e('Ver inscritos', 'dp-torneos'); ?></a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <div class="dp-dashboard-col dp-dashboard-col-full">
      <h2><?php _e( 'Últimas inscripciones', 'dp-torneos' ); ?></h2>
      <?php if ( empty( $ultimas_inscripciones ) ) : ?>
        <p class="dp-muted"><?php _e( 'Aún no hay inscripciones.', 'dp-torneos' ); ?></p>
      <?php else : ?>
        <table class="wp-list-table widefat fixed striped dp-table">
          <thead><tr>
            <th><?php _e('Nombre', 'dp-torneos'); ?></th>
            <th><?php _e('Evento', 'dp-torneos'); ?></th>
            <th><?php _e('Email', 'dp-torneos'); ?></th>
            <th><?php _e('Fecha', 'dp-torneos'); ?></th>
          </tr></thead>
          <tbody>
            <?php foreach ( $ultimas_inscripciones as $ins ) : ?>
              <tr>
                <td><?php echo esc_html( $ins->nombre . ' ' . $ins->apellidos ); ?></td>
                <td><?php echo esc_html( $ins->evento_nombre ?? '—' ); ?></td>
                <td><?php echo esc_html( $ins->email ?: '—' ); ?></td>
                <td><?php echo esc_html( date_i18n( 'd/m/Y H:i', strtotime( $ins->created_at ) ) ); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

  </div>
</div>
