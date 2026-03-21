<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap dp-torneos-wrap">
  <h1>⚙️ <?php _e('Ajustes', 'dp-torneos'); ?></h1>

  <?php if ( $saved ) : ?>
    <div class="notice notice-success is-dismissible"><p><?php _e('✅ Ajustes guardados.', 'dp-torneos'); ?></p></div>
  <?php endif; ?>

  <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
    <input type="hidden" name="action" value="dp_save_ajustes">
    <?php wp_nonce_field('dp_save_ajustes'); ?>

    <table class="form-table dp-form-table">
      <tr>
        <th><label for="email_admin"><?php _e('Email de administración', 'dp-torneos'); ?></label></th>
        <td>
          <input type="email" id="email_admin" name="email_admin" class="regular-text"
            value="<?php echo esc_attr(get_option('dp_torneos_email_admin', get_option('admin_email'))); ?>">
          <p class="description"><?php _e('Email que recibirá notificaciones de nuevas inscripciones.', 'dp-torneos'); ?></p>
        </td>
      </tr>
      <tr>
        <th><?php _e('Notificaciones por email', 'dp-torneos'); ?></th>
        <td>
          <label>
            <input type="checkbox" name="email_notificar" value="1"
              <?php checked(get_option('dp_torneos_email_notificar', '1'), '1'); ?>>
            <?php _e('Notificar al administrador cuando se reciba una nueva inscripción', 'dp-torneos'); ?>
          </label>
        </td>
      </tr>
      <tr>
        <th><label for="moneda"><?php _e('Moneda', 'dp-torneos'); ?></label></th>
        <td>
          <select id="moneda" name="moneda">
            <option value="EUR" <?php selected(get_option('dp_torneos_moneda','EUR'), 'EUR'); ?>>EUR (€)</option>
            <option value="USD" <?php selected(get_option('dp_torneos_moneda','EUR'), 'USD'); ?>>USD ($)</option>
            <option value="GBP" <?php selected(get_option('dp_torneos_moneda','EUR'), 'GBP'); ?>>GBP (£)</option>
          </select>
        </td>
      </tr>
    </table>

    <p class="submit">
      <button type="submit" class="button button-primary">💾 <?php _e('Guardar ajustes', 'dp-torneos'); ?></button>
    </p>
  </form>

  <hr>
  <h2><?php _e('Shortcodes disponibles', 'dp-torneos'); ?></h2>
  <table class="wp-list-table widefat dp-table">
    <thead><tr><th><?php _e('Shortcode', 'dp-torneos'); ?></th><th><?php _e('Descripción', 'dp-torneos'); ?></th></tr></thead>
    <tbody>
      <tr><td><code>[dp_torneo_calendario]</code></td><td><?php _e('Muestra el calendario de eventos públicos. Parámetros: provincia="Murcia" limite="10"', 'dp-torneos'); ?></td></tr>
      <tr><td><code>[dp_torneo_inscripcion id="X"]</code></td><td><?php _e('Formulario de inscripción para el evento con ID X.', 'dp-torneos'); ?></td></tr>
      <tr><td><code>[dp_torneo_ficha id="X"]</code></td><td><?php _e('Ficha pública completa del evento con ID X.', 'dp-torneos'); ?></td></tr>
    </tbody>
  </table>
</div>
