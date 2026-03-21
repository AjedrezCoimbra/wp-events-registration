<?php if ( ! defined( 'ABSPATH' ) ) exit;
$cuota = $evento->cuota_inscripcion ? number_format($evento->cuota_inscripcion,2).' €' : __('Gratuito','wp-events-registration');
?>
<div class="wper-ficha">
  <h2 class="wper-ficha-titulo"><?php echo esc_html($evento->nombre); ?></h2>

  <div class="wper-ficha-badges">
    <span class="wper-cal-estado wper-estado-<?php echo esc_attr($evento->estado); ?>">
      <?php echo esc_html(ucfirst($evento->estado)); ?>
    </span>
    <span class="wper-cal-modalidad"><?php echo esc_html($evento->modalidad); ?></span>
  </div>

  <div class="wper-ficha-grid">
    <div class="wper-ficha-item">
      <span class="wper-ficha-label"><?php _e('Fecha del evento', 'wp-events-registration'); ?></span>
      <span class="wper-ficha-value">
        <?php echo date_i18n('d/m/Y', strtotime($evento->fecha_inicio)); ?>
        <?php if ($evento->fecha_inicio !== $evento->fecha_fin): ?> → <?php echo date_i18n('d/m/Y', strtotime($evento->fecha_fin)); ?><?php endif; ?>
      </span>
    </div>
    <div class="wper-ficha-item">
      <span class="wper-ficha-label"><?php _e('Lugar', 'wp-events-registration'); ?></span>
      <span class="wper-ficha-value"><?php echo esc_html($evento->poblacion.', '.$evento->provincia); ?></span>
    </div>
    <?php if ($evento->numero_rondas): ?>
    <div class="wper-ficha-item">
      <span class="wper-ficha-label"><?php _e('Rondas', 'wp-events-registration'); ?></span>
      <span class="wper-ficha-value"><?php echo esc_html($evento->numero_rondas); ?></span>
    </div>
    <?php endif; ?>
    <div class="wper-ficha-item">
      <span class="wper-ficha-label"><?php _e('Cuota', 'wp-events-registration'); ?></span>
      <span class="wper-ficha-value"><?php echo esc_html($cuota); ?></span>
    </div>
    <div class="wper-ficha-item">
      <span class="wper-ficha-label"><?php _e('Inscripción', 'wp-events-registration'); ?></span>
      <span class="wper-ficha-value">
        <?php echo date_i18n('d/m/Y', strtotime($evento->fecha_inicio_inscripcion)); ?>
        → <?php echo date_i18n('d/m/Y', strtotime($evento->fecha_fin_inscripcion)); ?>
      </span>
    </div>
    <?php if ($evento->url_bases): ?>
    <div class="wper-ficha-item wper-ficha-item-full">
      <span class="wper-ficha-label"><?php _e('Bases del evento', 'wp-events-registration'); ?></span>
      <span class="wper-ficha-value">
        <a href="<?php echo esc_url($evento->url_bases); ?>" target="_blank" rel="noopener">
          🔗 <?php echo esc_html($evento->url_bases); ?>
        </a>
      </span>
    </div>
    <?php endif; ?>
  </div>

  <?php if ( ! empty( $evento->observaciones ) ) : ?>
    <div class="wper-ficha-observaciones">
      <span class="wper-ficha-label"><?php _e('Más información', 'wp-events-registration'); ?></span>
      <div class="wper-ficha-value"><?php echo wp_kses_post( $evento->observaciones ); ?></div>
    </div>
  <?php endif; ?>

  <?php if ($evento->google_maps): ?>
    <div class="wper-ficha-map">
      <iframe src="<?php echo esc_url($evento->google_maps); ?>"
        width="100%" height="300" style="border:0;" allowfullscreen loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
  <?php endif; ?>

  <?php if ($evento->estado === 'abierto'): ?>
    <div class="wper-ficha-inscripcion">
      <h3><?php _e('Inscribirse al evento', 'wp-events-registration'); ?></h3>
      <?php echo do_shortcode('[wper_inscripcion id="'.$evento->id.'"]'); ?>
    </div>
  <?php endif; ?>
</div>
