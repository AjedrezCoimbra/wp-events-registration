<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wper-inscripcion-wrap" id="wper-form-<?php echo $evento->id; ?>">

  <div class="wper-form-evento-info">
    <strong><?php echo esc_html($evento->nombre); ?></strong>
    &nbsp;·&nbsp; <?php echo esc_html($evento->poblacion.', '.$evento->provincia); ?>
    &nbsp;·&nbsp; <?php echo date_i18n('d/m/Y', strtotime($evento->fecha_inicio)); ?>
  </div>

  <?php if ( $evento->estado === 'cerrado' ) : ?>
    <div class="wper-aviso wper-aviso-cerrado">
      <?php _e('Las inscripciones para este evento están cerradas.', 'wp-events-registration'); ?>
    </div>

  <?php else : ?>
    <div class="wper-form-msg" id="wper-msg-<?php echo $evento->id; ?>" style="display:none;"></div>

    <form class="wper-form wper-form-inscripcion" data-evento-id="<?php echo $evento->id; ?>">

      <div class="wper-form-row">
        <div class="wper-form-group">
          <label for="wper-nombre-<?php echo $evento->id; ?>"><?php _e('Nombre', 'wp-events-registration'); ?> <span class="wper-required">*</span></label>
          <input type="text" id="wper-nombre-<?php echo $evento->id; ?>" name="nombre" required placeholder="<?php esc_attr_e('Tu nombre', 'wp-events-registration'); ?>">
        </div>
        <div class="wper-form-group">
          <label for="wper-apellidos-<?php echo $evento->id; ?>"><?php _e('Apellidos', 'wp-events-registration'); ?> <span class="wper-required">*</span></label>
          <input type="text" id="wper-apellidos-<?php echo $evento->id; ?>" name="apellidos" required placeholder="<?php esc_attr_e('Tus apellidos', 'wp-events-registration'); ?>">
        </div>
      </div>

      <div class="wper-form-row">
        <div class="wper-form-group">
          <label for="wper-fide-<?php echo $evento->id; ?>"><?php _e('ID FIDE', 'wp-events-registration'); ?></label>
          <input type="text" id="wper-fide-<?php echo $evento->id; ?>" name="fide_id" placeholder="<?php esc_attr_e('Opcional', 'wp-events-registration'); ?>">
        </div>
        <div class="wper-form-group">
          <label for="wper-telefono-<?php echo $evento->id; ?>"><?php _e('Teléfono de contacto', 'wp-events-registration'); ?></label>
          <input type="tel" id="wper-telefono-<?php echo $evento->id; ?>" name="telefono" placeholder="600 000 000">
        </div>
      </div>

      <div class="wper-form-row">
        <div class="wper-form-group">
          <label for="wper-email-<?php echo $evento->id; ?>"><?php _e('Email', 'wp-events-registration'); ?></label>
          <input type="email" id="wper-email-<?php echo $evento->id; ?>" name="email" placeholder="tu@email.com">
        </div>
        <div class="wper-form-group wper-form-group-check">
          <label class="wper-check-label">
            <input type="checkbox" name="alojamiento" value="1" id="wper-aloj-<?php echo $evento->id; ?>">
            <?php _e('¿Te alojarás?', 'wp-events-registration'); ?>
          </label>
        </div>
      </div>

      <div class="wper-form-row">
        <div class="wper-form-group">
          <label for="wper-obs-<?php echo $evento->id; ?>"><?php _e('Observaciones', 'wp-events-registration'); ?></label>
          <textarea id="wper-obs-<?php echo $evento->id; ?>" name="observaciones" rows="3" placeholder="<?php esc_attr_e('Indica aquí cualquier observación relevante...', 'wp-events-registration'); ?>" style="width:100%; border:1px solid #ddd; border-radius:4px; padding:8px;"></textarea>
        </div>
      </div>

      <div class="wper-form-submit">
        <button type="submit" class="wper-btn wper-btn-primary">
          ✅ <?php _e('Confirmar inscripción', 'wp-events-registration'); ?>
        </button>
      </div>

    </form>
  <?php endif; ?>
</div>
