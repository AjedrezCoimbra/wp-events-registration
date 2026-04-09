<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPER_Public {

    public function init() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    public function enqueue_assets() {
        // Solo cargar si la página tiene un shortcode del plugin
        global $post;
        if ( ! is_a( $post, 'WP_Post' ) ) return;

        $has_shortcode =
            has_shortcode( $post->post_content, 'wper_calendario' ) ||
            has_shortcode( $post->post_content, 'wper_inscripcion' ) ||
            has_shortcode( $post->post_content, 'wper_ficha' ) ||
            has_shortcode( $post->post_content, 'wper_test' );

        if ( ! $has_shortcode ) return;

        wp_enqueue_style(
            'wper-public',
            WPER_PLUGIN_URL . 'public/assets/public.css',
            array(),
            WPER_VERSION
        );

        wp_enqueue_script(
            'wper-public',
            WPER_PLUGIN_URL . 'public/assets/public.js',
            array('jquery'),
            WPER_VERSION,
            true
        );

        wp_localize_script( 'wper-public', 'wperData', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'wper_inscribir_nonce' ),
            'i18n'     => array(
                'enviando'  => __( 'Enviando...', 'wp-events-registration' ),
                'error_gen' => __( 'Error al procesar la inscripción.', 'wp-events-registration' ),
                'inscritos_en' => __( 'Inscritos en', 'wp-events-registration' ),
                'cargando'     => __( 'Cargando...', 'wp-events-registration' ),
            ),
        ) );
    }

    public static function render_event_card($ev) {
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
              <?php if ( ! empty($ev->ritmo_juego) ) : ?>
                <span class="wper-ritmo-badge"><?php echo esc_html( $ev->ritmo_juego ); ?></span>
              <?php endif; ?>
              <?php if ( ! empty($ev->tiempo_juego) ) : ?>
                <span class="wper-tiempo-text"><?php echo esc_html( $ev->tiempo_juego ); ?></span>
              <?php endif; ?>
              <?php if ( ! empty($ev->elo_fide) ) : ?>
                <span class="wper-fide-badge"><?php _e( 'ELO FIDE', 'wp-events-registration' ); ?></span>
              <?php endif; ?>
              <?php if ( ! empty($ev->subvencionable) ) : ?>
                <span class="wper-subvencionable-badge"><?php _e( 'Subvencionable', 'wp-events-registration' ); ?></span>
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
                <span><?php printf( __('Fin de inscripciones: %s', 'wp-events-registration'), date_i18n('d/m/Y', strtotime($ev->fecha_fin_inscripcion)) ); ?></span>
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
                <button type="button" class="wper-btn wper-btn-primary wper-open-inscripcion-modal" 
                        data-target="#wper-form-wrapper-<?php echo $ev->id; ?>">
                  ✅ <?php _e('Inscribirse', 'wp-events-registration'); ?>
                </button>
                <button type="button" class="wper-btn wper-btn-listado wper-open-inscritos-modal" 
                        data-evento-id="<?php echo $ev->id; ?>" 
                        data-evento-nombre="<?php echo esc_attr($ev->nombre); ?>">
                  👁️ <?php _e('Ver inscritos', 'wp-events-registration'); ?>
                </button>
              <?php else: ?>
                <span class="wper-btn wper-btn-disabled"><?php _e('Inscripción cerrada', 'wp-events-registration'); ?></span>
                <button type="button" class="wper-btn wper-btn-listado wper-open-inscritos-modal" 
                        data-evento-id="<?php echo $ev->id; ?>" 
                        data-evento-nombre="<?php echo esc_attr($ev->nombre); ?>">
                  👁️ <?php _e('Ver inscritos', 'wp-events-registration'); ?>
                </button>
              <?php endif; ?>
            </div>

            <?php if ($abierto): ?>
              <div id="wper-form-wrapper-<?php echo $ev->id; ?>" class="wper-inscripcion-hidden-form" style="display:none;">
                <?php echo do_shortcode('[wper_inscripcion id="'.$ev->id.'"]'); ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <?php
    }
}
