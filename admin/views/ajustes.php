<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap wper-wrap">
  <h1>⚙️ <?php _e('Ajustes', 'wp-events-registration'); ?></h1>

  <?php if ( $saved ) : ?>
    <div class="notice notice-success is-dismissible"><p><?php _e('✅ Ajustes guardados.', 'wp-events-registration'); ?></p></div>
  <?php endif; ?>

  <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
    <input type="hidden" name="action" value="wper_save_ajustes">
    <?php wp_nonce_field('wper_save_ajustes'); ?>

    <table class="form-table dp-form-table">
      <tr>
        <th><label for="email_admin"><?php _e('Email de administración', 'wp-events-registration'); ?></label></th>
        <td>
          <input type="email" id="email_admin" name="email_admin" class="regular-text"
            value="<?php echo esc_attr(get_option('wper_email_admin', get_option('admin_email'))); ?>">
          <p class="description"><?php _e('Email que recibirá notificaciones de nuevas inscripciones.', 'wp-events-registration'); ?></p>
        </td>
      </tr>
      <tr>
        <th><?php _e('Notificaciones por email', 'wp-events-registration'); ?></th>
        <td>
          <label>
            <input type="checkbox" name="email_notificar" value="1"
              <?php checked(get_option('wper_email_notificar', '1'), '1'); ?>>
            <?php _e('Notificar al administrador cuando se reciba una nueva inscripción', 'wp-events-registration'); ?>
          </label>
        </td>
      </tr>
      <tr>
        <th><label for="moneda"><?php _e('Moneda', 'wp-events-registration'); ?></label></th>
        <td>
          <select id="moneda" name="moneda">
            <option value="EUR" <?php selected(get_option('wper_moneda','EUR'), 'EUR'); ?>>EUR (€)</option>
            <option value="USD" <?php selected(get_option('wper_moneda','EUR'), 'USD'); ?>>USD ($)</option>
            <option value="GBP" <?php selected(get_option('wper_moneda','EUR'), 'GBP'); ?>>GBP (£)</option>
          </select>
        </td>
      </tr>
    </table>

    <p class="submit">
      <button type="submit" class="button button-primary">💾 <?php _e('Guardar ajustes', 'wp-events-registration'); ?></button>
    </p>
  </form>

  <hr>
  <h2><?php _e('Shortcodes disponibles', 'wp-events-registration'); ?></h2>
  <table class="wp-list-table widefat dp-table">
    <thead><tr><th><?php _e('Shortcode', 'wp-events-registration'); ?></th><th><?php _e('Descripción', 'wp-events-registration'); ?></th></tr></thead>
    <tbody>
      <tr><td><code>[wper_calendario]</code></td><td><?php _e('Muestra el calendario de eventos públicos. Parámetros: provincia="Murcia" limite="10"', 'wp-events-registration'); ?></td></tr>
      <tr><td><code>[wper_inscripcion id="X"]</code></td><td><?php _e('Formulario de inscripción para el evento con ID X.', 'wp-events-registration'); ?></td></tr>
      <tr><td><code>[wper_ficha id="X"]</code></td><td><?php _e('Ficha pública completa del evento con ID X.', 'wp-events-registration'); ?></td></tr>
    </tbody>
  </table>
</div>
