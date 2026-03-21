<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="dp-calendario">
  <h2 class="dp-cal-title"><?php _e('Calendario de Eventos', 'wp-events-registration'); ?></h2>

  <?php if ( empty( $eventos ) ) : ?>
    <p class="dp-cal-empty"><?php _e('No hay eventos disponibles en este momento.', 'wp-events-registration'); ?></p>
  <?php else : ?>
    <div class="dp-cal-grid">
      <?php foreach ( $eventos as $ev ) :
        $abierto = $ev->estado === 'abierto';
        $cuota   = $ev->cuota_inscripcion ? number_format($ev->cuota_inscripcion,2).' €' : __('Gratuito','wp-events-registration');
        $rondas  = $ev->numero_rondas ? $ev->numero_rondas . ' ' . __('rondas','wp-events-registration') : null;
      ?>
        <div class="dp-cal-card dp-cal-<?php echo esc_attr($ev->estado); ?>">
          <div class="dp-cal-card-head">
            <span class="dp-cal-estado dp-estado-<?php echo esc_attr($ev->estado); ?>">
              <?php echo $abierto ? __('Inscripción abierta','wp-events-registration') : __('Cerrado','wp-events-registration'); ?>
            </span>
            <span class="dp-cal-modalidad"><?php echo esc_html($ev->modalidad); ?></span>
          </div>

          <h3 class="dp-cal-nombre"><?php echo esc_html($ev->nombre); ?></h3>

          <div class="dp-cal-meta">
            <div class="dp-cal-meta-item">
              <span class="dp-meta-icon">📅</span>
              <span><?php echo date_i18n('d/m/Y', strtotime($ev->fecha_inicio)); ?>
                <?php if ($ev->fecha_inicio !== $ev->fecha_fin): ?>
                  → <?php echo date_i18n('d/m/Y', strtotime($ev->fecha_fin)); ?>
                <?php endif; ?>
              </span>
            </div>
            <div class="dp-cal-meta-item">
              <span class="dp-meta-icon">📍</span>
              <span><?php echo esc_html($ev->poblacion.', '.$ev->provincia); ?></span>
            </div>
            <?php if ($rondas): ?>
            <div class="dp-cal-meta-item">
              <span class="dp-meta-icon">♟</span>
              <span><?php echo esc_html($rondas); ?></span>
            </div>
            <?php endif; ?>
            <div class="dp-cal-meta-item">
              <span class="dp-meta-icon">💶</span>
              <span><?php echo esc_html($cuota); ?></span>
            </div>
            <?php if ($abierto): ?>
            <div class="dp-cal-meta-item">
              <span class="dp-meta-icon">🗓</span>
              <span><?php _e('Inscripción hasta','wp-events-registration'); ?>: <?php echo date_i18n('d/m/Y', strtotime($ev->fecha_fin_inscripcion)); ?></span>
            </div>
            <?php endif; ?>
            <?php if ( ! empty( $ev->observaciones ) ) : ?>
              <div class="dp-cal-meta-item">
                <span class="dp-meta-icon">ℹ️</span>
                <span style="font-size:0.85em; opacity:0.8;"><?php echo wp_trim_words( esc_html( $ev->observaciones ), 12 ); ?></span>
              </div>
            <?php endif; ?>
          </div>

          <div class="dp-cal-actions">
            <?php if ($ev->url_bases): ?>
              <a href="<?php echo esc_url($ev->url_bases); ?>" target="_blank" class="dp-btn dp-btn-outline">
                📄 <?php _e('Ver bases', 'wp-events-registration'); ?>
              </a>
            <?php endif; ?>

            <?php if ($abierto): ?>
              <a href="#dp-form-<?php echo $ev->id; ?>" class="dp-btn dp-btn-primary">
                ✅ <?php _e('Inscribirse', 'wp-events-registration'); ?>
              </a>
            <?php else: ?>
              <span class="dp-btn dp-btn-disabled"><?php _e('Inscripción cerrada', 'wp-events-registration'); ?></span>
            <?php endif; ?>
          </div>

          <?php if ($abierto): ?>
            <div id="dp-form-<?php echo $ev->id; ?>" class="dp-cal-form-inline" style="display:none;">
              <?php echo do_shortcode('[wper_inscripcion id="'.$ev->id.'"]'); ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
