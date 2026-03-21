<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="dp-inscripcion-wrap" id="dp-form-<?php echo $evento->id; ?>">

  <div class="dp-form-evento-info">
    <strong><?php echo esc_html($evento->nombre); ?></strong>
    &nbsp;·&nbsp; <?php echo esc_html($evento->poblacion.', '.$evento->provincia); ?>
    &nbsp;·&nbsp; <?php echo date_i18n('d/m/Y', strtotime($evento->fecha_inicio)); ?>
  </div>

  <?php if ( $evento->estado === 'cerrado' ) : ?>
    <div class="dp-aviso dp-aviso-cerrado">
      <?php _e('Las inscripciones para este evento están cerradas.', 'dp-torneos'); ?>
    </div>

  <?php else : ?>
    <div class="dp-form-msg" id="dp-msg-<?php echo $evento->id; ?>" style="display:none;"></div>

    <form class="dp-form dp-form-inscripcion" data-evento-id="<?php echo $evento->id; ?>">

      <div class="dp-form-row">
        <div class="dp-form-group">
          <label for="dp-nombre-<?php echo $evento->id; ?>"><?php _e('Nombre', 'dp-torneos'); ?> <span class="dp-required">*</span></label>
          <input type="text" id="dp-nombre-<?php echo $evento->id; ?>" name="nombre" required placeholder="<?php esc_attr_e('Tu nombre', 'dp-torneos'); ?>">
        </div>
        <div class="dp-form-group">
          <label for="dp-apellidos-<?php echo $evento->id; ?>"><?php _e('Apellidos', 'dp-torneos'); ?> <span class="dp-required">*</span></label>
          <input type="text" id="dp-apellidos-<?php echo $evento->id; ?>" name="apellidos" required placeholder="<?php esc_attr_e('Tus apellidos', 'dp-torneos'); ?>">
        </div>
      </div>

      <div class="dp-form-row">
        <div class="dp-form-group">
          <label for="dp-fide-<?php echo $evento->id; ?>"><?php _e('ID FIDE', 'dp-torneos'); ?></label>
          <input type="text" id="dp-fide-<?php echo $evento->id; ?>" name="fide_id" placeholder="<?php esc_attr_e('Opcional', 'dp-torneos'); ?>">
        </div>
        <div class="dp-form-group">
          <label for="dp-telefono-<?php echo $evento->id; ?>"><?php _e('Teléfono de contacto', 'dp-torneos'); ?></label>
          <input type="tel" id="dp-telefono-<?php echo $evento->id; ?>" name="telefono" placeholder="+34 600 000 000">
        </div>
      </div>

      <div class="dp-form-row">
        <div class="dp-form-group">
          <label for="dp-email-<?php echo $evento->id; ?>"><?php _e('Email', 'dp-torneos'); ?></label>
          <input type="email" id="dp-email-<?php echo $evento->id; ?>" name="email" placeholder="tu@email.com">
        </div>
        <div class="dp-form-group dp-form-group-check">
          <label class="dp-check-label">
            <input type="checkbox" name="alojamiento" value="1" id="dp-aloj-<?php echo $evento->id; ?>">
            <?php _e('¿Necesito alojamiento?', 'dp-torneos'); ?>
          </label>
        </div>
      </div>

      <div class="dp-form-submit">
        <button type="submit" class="dp-btn dp-btn-primary">
          ✅ <?php _e('Confirmar inscripción', 'dp-torneos'); ?>
        </button>
      </div>

    </form>
  <?php endif; ?>
</div>
