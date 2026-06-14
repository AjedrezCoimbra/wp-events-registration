<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="wper-calendario">
  <h2 class="wper-cal-title"><?php _e('Calendario de Eventos', 'wp-events-registration'); ?></h2>

  <?php
  // Determinar pestaña activa si hay paginación
  $tab_active = 'open';
  if ( isset($_GET['wper_paged_c']) ) $tab_active = 'closed';
  if ( isset($_GET['wper_paged_f']) ) $tab_active = 'finished';
  ?>

  <div class="wper-cal-tabs">
    <button class="wper-tab-btn <?php echo $tab_active === 'open' ? 'active' : ''; ?>" data-tab="open">
        🟢 <?php _e('Inscripción Abierta', 'wp-events-registration'); ?>
    </button>
    <button class="wper-tab-btn <?php echo $tab_active === 'closed' ? 'active' : ''; ?>" data-tab="closed">
        🔒 <?php _e('Inscripción Cerrada', 'wp-events-registration'); ?>
    </button>
    <button class="wper-tab-btn <?php echo $tab_active === 'finished' ? 'active' : ''; ?>" data-tab="finished">
        🏁 <?php _e('Eventos Finalizados', 'wp-events-registration'); ?>
    </button>
  </div>

  <!-- 1. ABIERTOS -->
  <div id="tab-open" class="wper-tab-content <?php echo $tab_active === 'open' ? 'active' : ''; ?>">
    <?php if ( empty( $eventos_abiertos ) ) : ?>
      <p class="wper-cal-empty"><?php _e('No hay eventos con inscripción abierta.', 'wp-events-registration'); ?></p>
    <?php else : ?>
      <div class="wper-cal-grid">
        <?php foreach ( $eventos_abiertos as $ev ) : 
          WPER_Public::render_event_card($ev);
        endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- 2. CERRADOS (En curso / Próximos) -->
  <div id="tab-closed" class="wper-tab-content <?php echo $tab_active === 'closed' ? 'active' : ''; ?>">
    <?php if ( empty( $eventos_cerrados ) ) : ?>
      <p class="wper-cal-empty"><?php _e('No hay eventos en esta categoría.', 'wp-events-registration'); ?></p>
    <?php else : ?>
      <div class="wper-cal-grid">
        <?php foreach ( $eventos_cerrados as $ev ) : 
          WPER_Public::render_event_card($ev);
        endforeach; ?>
      </div>
      <?php if ( $total_pages_cerrados > 1 ) : ?>
        <div class="wper-pagination">
          <?php
          echo paginate_links( array(
              'base'      => add_query_arg( 'wper_paged_c', '%#%' ),
              'format'    => '',
              'current'   => $paged_cerrados,
              'total'     => $total_pages_cerrados,
              'prev_text' => '&laquo; ' . __('Anterior', 'wp-events-registration'),
              'next_text' => __('Siguiente', 'wp-events-registration') . ' &raquo;',
          ) );
          ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>

  <!-- 3. FINALIZADOS -->
  <div id="tab-finished" class="wper-tab-content <?php echo $tab_active === 'finished' ? 'active' : ''; ?>">
    <?php if ( empty( $eventos_finalizados ) ) : ?>
      <p class="wper-cal-empty"><?php _e('No hay eventos finalizados registrados.', 'wp-events-registration'); ?></p>
    <?php else : ?>
      <div class="wper-cal-grid">
        <?php foreach ( $eventos_finalizados as $ev ) : 
          WPER_Public::render_event_card($ev);
        endforeach; ?>
      </div>
      <?php if ( $total_pages_finalizados > 1 ) : ?>
        <div class="wper-pagination">
          <?php
          echo paginate_links( array(
              'base'      => add_query_arg( 'wper_paged_f', '%#%' ),
              'format'    => '',
              'current'   => $paged_finalizados,
              'total'     => $total_pages_finalizados,
              'prev_text' => '&laquo; ' . __('Anterior', 'wp-events-registration'),
              'next_text' => __('Siguiente', 'wp-events-registration') . ' &raquo;',
          ) );
          ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>


    <!-- Modal para Observaciones -->
    <div id="wper-modal-obs" class="wper-modal">
      <div class="wper-modal-content">
        <div class="wper-modal-header">
          <h3></h3>
          <span class="wper-modal-close">&times;</span>
        </div>
        <div class="wper-modal-body"></div>
      </div>
    </div>

    <!-- Modal para Inscripción -->
    <div id="wper-modal-inscripcion" class="wper-modal">
      <div class="wper-modal-content">
        <div class="wper-modal-header">
          <h3 style="margin:0;"><?php _e('Formulario de Inscripción', 'wp-events-registration'); ?></h3>
          <span class="wper-modal-close">&times;</span>
        </div>
        <div class="wper-modal-body">
            <!-- El contenido se cargará dinámicamente -->
        </div>
      </div>
    </div>

    <!-- Modal para Listado de Inscritos -->
    <div id="wper-modal-listado" class="wper-modal">
      <div class="wper-modal-content">
        <div class="wper-modal-header">
          <h3></h3>
          <span class="wper-modal-close">&times;</span>
        </div>
        <div class="wper-modal-body">
            <!-- El contenido se cargará dinámicamente -->
        </div>
      </div>
    </div>
</div>

