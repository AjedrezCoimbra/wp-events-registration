<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Aseguramos que $eventos sea un array
$eventos = is_array($eventos) ? $eventos : array();
?>

<div class="wper-calendario">
  <h2 class="wper-cal-title"><?php _e('Calendario de Eventos', 'wp-events-registration'); ?></h2>

  <div class="wper-cal-tabs">
    <button class="wper-tab-btn active" data-tab="open"><?php _e('Eventos Abiertos', 'wp-events-registration'); ?></button>
    <button class="wper-tab-btn" data-tab="closed"><?php _e('Eventos Cerrados', 'wp-events-registration'); ?></button>
  </div>

  <?php
  $eventos_abiertos = array_filter( $eventos, function($e) { return isset($e->estado) && $e->estado === 'abierto'; });
  $eventos_cerrados = array_filter( $eventos, function($e) { return isset($e->estado) && $e->estado === 'cerrado'; });
  ?>

  <div id="tab-open" class="wper-tab-content active">
    <?php if ( empty( $eventos_abiertos ) ) : ?>
      <p class="wper-cal-empty"><?php _e('No hay eventos abiertos en este momento.', 'wp-events-registration'); ?></p>
    <?php else : ?>
      <div class="wper-cal-grid">
        <?php foreach ( $eventos_abiertos as $ev ) : 
          WPER_Public::render_event_card($ev);
        endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div id="tab-closed" class="wper-tab-content">
    <?php if ( empty( $eventos_cerrados ) ) : ?>
      <p class="wper-cal-empty"><?php _e('No hay eventos cerrados.', 'wp-events-registration'); ?></p>
    <?php else : ?>
      <div class="wper-cal-grid">
        <?php foreach ( $eventos_cerrados as $ev ) : 
          WPER_Public::render_event_card($ev);
        endforeach; ?>
      </div>
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

