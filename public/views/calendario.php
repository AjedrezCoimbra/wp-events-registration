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
          <?php if ($ev->cartel_url): ?>
            <div class="dp-cal-poster">
              <img src="<?php echo esc_url($ev->cartel_url); ?>" alt="<?php echo esc_attr($ev->nombre); ?>" class="dp-cal-poster-img">
              <a href="<?php echo esc_url($ev->cartel_url); ?>" target="_blank" class="dp-cal-poster-zoom" title="<?php _e('Ver cartel completo', 'wp-events-registration'); ?>">🔍</a>
            </div>
          <?php endif; ?>

          <div class="dp-cal-content">
            <div class="dp-cal-card-head">
              <span class="dp-cal-estado dp-estado-<?php echo esc_attr($ev->estado); ?>">
                <?php echo $abierto ? __('Inscripción abierta','wp-events-registration') : __('Cerrado','wp-events-registration'); ?>
              </span>
              <span class="dp-cal-modalidad"><?php echo esc_html($ev->modalidad); ?></span>
            </div>

            <div class="dp-cal-nombre"><?php echo esc_html($ev->nombre); ?></div>

            <div class="dp-cal-meta">
              <div class="dp-cal-meta-item">
                <span class="dp-meta-icon">📅</span>
                <span>
                  <?php if ($ev->fecha_inicio === $ev->fecha_fin): ?>
                    <?php echo date_i18n('d/m/Y', strtotime($ev->fecha_inicio)); ?>
                  <?php else: ?>
                    <?php printf( __('De %s a %s', 'wp-events-registration'), date_i18n('d/m/Y', strtotime($ev->fecha_inicio)), date_i18n('d/m/Y', strtotime($ev->fecha_fin)) ); ?>
                  <?php endif; ?>
                </span>
              </div>
              <div class="dp-cal-meta-item">
                <span class="dp-meta-icon">📍</span>
                <span><?php echo esc_html($ev->poblacion.', '.$ev->provincia); ?></span>
                <?php if ($ev->google_maps): ?>
                  <a href="<?php echo esc_url($ev->google_maps); ?>" target="_blank" class="dp-btn-mini" title="<?php _e('Ver en Google Maps', 'wp-events-registration'); ?>">🗺️</a>
                <?php endif; ?>
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
                <span><?php printf( __('Inscripciones: De %s hasta %s', 'wp-events-registration'), date_i18n('d/m/Y', strtotime($ev->fecha_inicio_inscripcion)), date_i18n('d/m/Y', strtotime($ev->fecha_fin_inscripcion)) ); ?></span>
              </div>
              <?php endif; ?>
              
              <?php if ( ! empty( $ev->observaciones ) ) : ?>
                <div class="dp-cal-meta-item dp-cal-obs">
                  <span class="dp-meta-icon">ℹ️</span>
                  <div class="dp-obs-content"><?php echo wp_kses_post( $ev->observaciones ); ?></div>
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
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
