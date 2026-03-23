<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap wper-wrap">
  <h1>⚙️ <?php _e('Ajustes', 'wp-events-registration'); ?></h1>

  <?php if ( $saved ) : ?>
    <div class="notice notice-success is-dismissible"><p><?php _e('✅ Ajustes guardados.', 'wp-events-registration'); ?></p></div>
  <?php endif; ?>

  <?php if ( $checked ) : ?>
    <div class="notice notice-info is-dismissible"><p><?php _e('🔄 Caché de actualizaciones limpiado. Por favor, ve a <strong>Escritorio > Actualizaciones</strong> y pulsa "Comprobar de nuevo" para ver la última versión de GitHub.', 'wp-events-registration'); ?></p></div>
  <?php endif; ?>

  <div class="wper-dashboard-cols">
    <div class="wper-dashboard-col" style="flex: 2;">
      <h2><?php _e('Configuración General', 'wp-events-registration'); ?></h2>
      <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <input type="hidden" name="action" value="wper_save_ajustes">
        <?php wp_nonce_field('wper_save_ajustes'); ?>

        <table class="form-table wper-form-table">
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
          <tr>
            <th><label for="github_token">GitHub Token (opcional)</label></th>
            <td>
              <input type="password" name="github_token" id="github_token" 
                     value="<?php echo esc_attr(get_option('wper_github_token','')); ?>" 
                     class="regular-text">
              <p class="description">Personal Access Token para evitar límites de la API de GitHub.</p>
            </td>
          </tr>
        </table>

        <hr>
        <h2><?php _e('Plantillas de Correos', 'wp-events-registration'); ?></h2>
        <p class="description"><?php _e('Configura el contenido de los correos automáticos. Estos son los campos que puedes usar en el Expression Language:', 'wp-events-registration'); ?></p>
        <div style="background: #f0f0f1; padding: 10px; border-radius: 4px; border-left: 4px solid #0073aa; margin-bottom: 20px;">
          <code>{{nombre}}</code>, <code>{{apellidos}}</code>, <code>{{email}}</code>, <code>{{fide_id}}</code>, <code>{{telefono}}</code>, <code>{{alojamiento}}</code>, <code>{{observaciones}}</code>,<br>
          <code>{{evento_nombre}}</code>, <code>{{evento_fecha_inicio}}</code>, <code>{{evento_fecha_fin}}</code>, <code>{{evento_poblacion}}</code>, <code>{{evento_provincia}}</code>
        </div>

        <div class="wper-email-template-box" style="margin-bottom: 30px;">
          <h3><?php _e('1. Confirmación de Inscripción (al Usuario)', 'wp-events-registration'); ?></h3>
          <table class="form-table wper-form-table">
            <tr>
              <th><label><?php _e('Asunto', 'wp-events-registration'); ?></label></th>
              <td><input type="text" name="email_confirmacion_asunto" class="large-text" value="<?php echo esc_attr(get_option('wper_email_confirmacion_asunto')); ?>"></td>
            </tr>
            <tr>
              <th><label><?php _e('Cuerpo', 'wp-events-registration'); ?></label></th>
              <td>
                <?php wp_editor(get_option('wper_email_confirmacion_cuerpo'), 'email_confirmacion_cuerpo', array('textarea_name' => 'email_confirmacion_cuerpo', 'textarea_rows' => 10, 'media_buttons' => false)); ?>
              </td>
            </tr>
            <tr>
              <th><label><?php _e('CC (Copia)', 'wp-events-registration'); ?></label></th>
              <td><input type="text" name="email_confirmacion_cc" class="regular-text" value="<?php echo esc_attr(get_option('wper_email_confirmacion_cc')); ?>" placeholder="ej: copia@mail.com"></td>
            </tr>
            <tr>
              <th><label><?php _e('CCO (Copia oculta)', 'wp-events-registration'); ?></label></th>
              <td><input type="text" name="email_confirmacion_bcc" class="regular-text" value="<?php echo esc_attr(get_option('wper_email_confirmacion_bcc')); ?>" placeholder="ej: cco@mail.com"></td>
            </tr>
          </table>
        </div>

        <div class="wper-email-template-box">
          <h3><?php _e('2. Notificación de Nueva Inscripción (al Administrador)', 'wp-events-registration'); ?></h3>
          <table class="form-table wper-form-table">
            <tr>
              <th><label><?php _e('Para', 'wp-events-registration'); ?></label></th>
              <td><input type="email" name="email_notificacion_para" class="regular-text" value="<?php echo esc_attr(get_option('wper_email_notificacion_para', get_option('admin_email'))); ?>"></td>
            </tr>
            <tr>
              <th><label><?php _e('Asunto', 'wp-events-registration'); ?></label></th>
              <td><input type="text" name="email_notificacion_asunto" class="large-text" value="<?php echo esc_attr(get_option('wper_email_notificacion_asunto')); ?>"></td>
            </tr>
            <tr>
              <th><label><?php _e('Cuerpo', 'wp-events-registration'); ?></label></th>
              <td>
                <?php wp_editor(get_option('wper_email_notificacion_cuerpo'), 'email_notificacion_cuerpo', array('textarea_name' => 'email_notificacion_cuerpo', 'textarea_rows' => 10, 'media_buttons' => false)); ?>
              </td>
            </tr>
            <tr>
              <th><label><?php _e('CC (Copia)', 'wp-events-registration'); ?></label></th>
              <td><input type="text" name="email_notificacion_cc" class="regular-text" value="<?php echo esc_attr(get_option('wper_email_notificacion_cc')); ?>"></td>
            </tr>
            <tr>
              <th><label><?php _e('CCO (Copia oculta)', 'wp-events-registration'); ?></label></th>
              <td><input type="text" name="email_notificacion_bcc" class="regular-text" value="<?php echo esc_attr(get_option('wper_email_notificacion_bcc')); ?>"></td>
            </tr>
          </table>
        </div>

        <p class="submit">
          <button type="submit" class="button button-primary" style="padding: 10px 20px; height: auto;">💾 <?php _e('Guardar todos los ajustes', 'wp-events-registration'); ?></button>
        </p>
      </form>
    </div>

    <div class="wper-dashboard-col">
      <h2><?php _e('Sistema de Actualización', 'wp-events-registration'); ?></h2>
      <p><?php _e('El plugin busca automáticamente nuevas versiones en GitHub cada 12 horas.', 'wp-events-registration'); ?></p>
      <p><strong><?php _e('Versión actual:', 'wp-events-registration'); ?></strong> <code>v<?php echo WPER_VERSION; ?></code></p>
      <p><strong><?php _e('Compatibilidad WP:', 'wp-events-registration'); ?></strong> <code><?php _e('Hasta 6.4', 'wp-events-registration'); ?></code></p>
      
      <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <input type="hidden" name="action" value="wper_force_update_check">
        <?php wp_nonce_field('wper_force_update'); ?>
        <button type="submit" class="button button-secondary">
          🔄 <?php _e('Forzar comprobación de versión', 'wp-events-registration'); ?>
        </button>
      </form>
      <p class="description" style="margin-top:10px;">
        <?php _e('Usa este botón si acabas de subir una versión a GitHub y no aparece la notificación de actualización.', 'wp-events-registration'); ?>
      </p>
    </div>
  </div>

  <hr>
  <h2><?php _e('Shortcodes disponibles', 'wp-events-registration'); ?></h2>
  <table class="wp-list-table widefat wper-table">
    <thead><tr><th><?php _e('Shortcode', 'wp-events-registration'); ?></th><th><?php _e('Descripción', 'wp-events-registration'); ?></th></tr></thead>
    <tbody>
      <tr><td><code>[wper_calendario]</code></td><td><?php _e('Muestra el calendario de eventos públicos. Parámetros: provincia="Murcia" limite="10"', 'wp-events-registration'); ?></td></tr>
      <tr><td><code>[wper_inscripcion id="X"]</code></td><td><?php _e('Formulario de inscripción para el evento con ID X.', 'wp-events-registration'); ?></td></tr>
      <tr><td><code>[wper_ficha id="X"]</code></td><td><?php _e('Ficha pública completa del evento con ID X.', 'wp-events-registration'); ?></td></tr>
    </tbody>
  </table>
</div>
