<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wper-calendario">
  <h2 class="wper-cal-title"><?php _e('Calendario de Eventos', 'wp-events-registration'); ?></h2>

  <?php if ( empty( $eventos ) ) : ?>
    <p class="wper-cal-empty"><?php _e('No hay eventos disponibles en este momento.', 'wp-events-registration'); ?></p>
  <?php else : ?>
    <div class="wper-cal-grid">
      <?php foreach ( $eventos as $ev ) :
        $abierto = $ev->estado === 'abierto';
        $cuota   = $ev->cuota_inscripcion ? number_format($ev->cuota_inscripcion,2).' €' : __('Gratuito','wp-events-registration');
        $rondas  = $ev->numero_rondas ? $ev->numero_rondas . ' ' . __('rondas','wp-events-registration') : null;
        $poster  = $ev->cartel_url ?: WPER_PLUGIN_URL . 'public/assets/images/default-poster.png';
      ?>
        <div class="wper-cal-card wper-cal-<?php echo esc_attr($ev->estado); ?>">
          <div class="wper-cal-poster">
            <img src="<?php echo esc_url($poster); ?>" alt="<?php echo esc_attr($ev->nombre); ?>" class="wper-cal-poster-img">
            <?php if ($ev->cartel_url): ?>
              <a href="<?php echo esc_url($ev->cartel_url); ?>" target="_blank" class="wper-cal-poster-zoom" title="<?php _e('Ver cartel completo', 'wp-events-registration'); ?>">🔍</a>
            <?php endif; ?>
          </div>

          <div class="wper-cal-content">
            <div class="wper-cal-card-head">
              <span class="wper-cal-estado wper-estado-<?php echo esc_attr($ev->estado); ?>">
                <?php echo $abierto ? __('Inscripción abierta','wp-events-registration') : __('Cerrado','wp-events-registration'); ?>
              </span>
              <span class="wper-cal-modalidad"><?php echo esc_html($ev->modalidad); ?></span>
            </div>

            <div class="wper-cal-nombre"><?php echo esc_html($ev->nombre); ?></div>

            <div class="wper-cal-extra-meta">
              <?php if ( $ev->ritmo_juego ) : ?>
                <span class="wper-ritmo-badge"><?php echo esc_html( $ev->ritmo_juego ); ?></span>
              <?php endif; ?>
              <?php if ( $ev->tiempo_juego ) : ?>
                <span class="wper-tiempo-text"><?php echo esc_html( $ev->tiempo_juego ); ?></span>
              <?php endif; ?>
              <?php if ( $ev->elo_fide ) : ?>
                <span class="wper-fide-badge"><?php _e( 'ELO FIDE', 'wp-events-registration' ); ?></span>
              <?php endif; ?>
            </div>

            <div class="wper-cal-meta">
              <div class="wper-cal-meta-item">
                <span class="wper-meta-icon">📅</span>
                <span>
                  <?php if ($ev->fecha_inicio === $ev->fecha_fin): ?>
                    <?php echo date_i18n('d/m/Y', strtotime($ev->fecha_inicio)); ?>
                  <?php else: ?>
                    <?php printf( __('Del %s hasta %s', 'wp-events-registration'), date_i18n('d/m/Y', strtotime($ev->fecha_inicio)), date_i18n('d/m/Y', strtotime($ev->fecha_fin)) ); ?>
                  <?php endif; ?>
                </span>
              </div>
              <div class="wper-cal-meta-item">
                <span class="wper-meta-icon">📍</span>
                <span><?php echo esc_html($ev->poblacion.', '.$ev->provincia); ?></span>
                <?php if ($ev->google_maps): ?>
                  <a href="<?php echo esc_url($ev->google_maps); ?>" target="_blank" class="wper-btn-mini">
                    🗺️ <?php _e('Mapa', 'wp-events-registration'); ?>
                  </a>
                <?php endif; ?>
              </div>
              <?php if ($rondas): ?>
              <div class="wper-cal-meta-item">
                <span class="wper-meta-icon">♟</span>
                <span><?php echo esc_html($rondas); ?></span>
              </div>
              <?php endif; ?>
              <div class="wper-cal-meta-item">
                <span class="wper-meta-icon">💶</span>
                <span><?php echo esc_html($cuota); ?></span>
              </div>
              <div class="wper-cal-meta-item">
                <span class="wper-meta-icon">🗓</span>
                <span><?php printf( __('Inscripciones: Del %s hasta %s', 'wp-events-registration'), date_i18n('d/m/Y', strtotime($ev->fecha_inicio_inscripcion)), date_i18n('d/m/Y', strtotime($ev->fecha_fin_inscripcion)) ); ?></span>
              </div>
              
              <?php if ( ! empty( $ev->observaciones ) ) : ?>
                <div class="wper-cal-meta-item wper-cal-obs">
                  <span class="wper-meta-icon">ℹ️</span>
                  <div class="wper-obs-wrap">
                    <div class="wper-obs-preview">
                      <?php echo wp_strip_all_tags( $ev->observaciones ); ?>
                    </div>
                    <span class="wper-btn-obs-more" 
                          data-title="<?php echo esc_attr($ev->nombre); ?>" 
                          data-content="<?php echo esc_attr(wp_kses_post($ev->observaciones)); ?>">
                      <?php _e('Leer más...', 'wp-events-registration'); ?>
                    </span>
                  </div>
                </div>
              <?php endif; ?>
            </div>

            <div class="wper-cal-actions">
              <?php if ($ev->url_bases): ?>
                <a href="<?php echo esc_url($ev->url_bases); ?>" target="_blank" class="wper-btn wper-btn-outline">
                  📄 <?php _e('Ver bases', 'wp-events-registration'); ?>
                </a>
              <?php endif; ?>

              <?php if ($abierto): ?>
                <a href="#wper-form-<?php echo $ev->id; ?>" class="wper-btn wper-btn-primary">
                  ✅ <?php _e('Inscribirse', 'wp-events-registration'); ?>
                </a>
              <?php else: ?>
                <span class="wper-btn wper-btn-disabled"><?php _e('Inscripción cerrada', 'wp-events-registration'); ?></span>
              <?php endif; ?>
            </div>

            <?php if ($abierto): ?>
              <div id="wper-form-<?php echo $ev->id; ?>" class="wper-cal-form-inline" style="display:none;">
                <?php echo do_shortcode('[wper_inscripcion id="'.$ev->id.'"]'); ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
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

  <?php endif; ?>
</div>
