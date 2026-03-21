<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap wper-wrap">
  <h1>👥 <?php _e('Inscripciones', 'wp-events-registration'); ?></h1>

  <?php if ( $mensaje === 'ins_eliminada' ) : ?>
    <div class="notice notice-success is-dismissible"><p><?php _e('🗑️ Inscripción eliminada.', 'wp-events-registration'); ?></p></div>
  <?php endif; ?>

  <!-- Filtro por evento -->
  <div class="dp-filter-bar">
    <form method="get">
      <input type="hidden" name="page" value="wper-inscripciones">
      <select name="evento_id" onchange="this.form.submit()">
        <option value=""><?php _e('— Todos los eventos —', 'wp-events-registration'); ?></option>
        <?php foreach ( $eventos_lista as $ev ) : ?>
          <option value="<?php echo $ev->id; ?>" <?php selected($evento_id, $ev->id); ?>>
            <?php echo esc_html($ev->nombre); ?> (<?php echo date_i18n('d/m/Y', strtotime($ev->fecha_inicio)); ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </form>

    <?php if ( $evento_id && $evento ) : ?>
      <div class="dp-export-btns">
        <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=wper_export_pdf&evento_id='.$evento_id), 'wper_export_pdf_'.$evento_id); ?>"
           class="button" target="_blank">📄 <?php _e('Exportar PDF', 'wp-events-registration'); ?></a>
        <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=wper_export_csv&evento_id='.$evento_id), 'wper_export_csv_'.$evento_id); ?>"
           class="button">📊 <?php _e('Exportar CSV', 'wp-events-registration'); ?></a>
      </div>
    <?php endif; ?>
  </div>

  <?php if ( $evento_id && $evento ) : ?>
    <div class="dp-evento-info-bar">
      <strong><?php echo esc_html($evento->nombre); ?></strong>
      &nbsp;·&nbsp; <?php echo esc_html(ucfirst($evento->estado)); ?>
      &nbsp;·&nbsp; <?php echo date_i18n('d/m/Y', strtotime($evento->fecha_inicio)); ?>
      &nbsp;·&nbsp; <?php echo esc_html($evento->poblacion.', '.$evento->provincia); ?>
      &nbsp;·&nbsp; <strong><?php echo count($inscripciones); ?> <?php _e('inscritos', 'wp-events-registration'); ?></strong>
    </div>
  <?php endif; ?>

  <div class="dp-table-wrap">
  <table class="wp-list-table widefat fixed striped dp-table">
    <thead><tr>
      <?php if ( ! $evento_id ) : ?><th><?php _e('Evento', 'wp-events-registration'); ?></th><?php endif; ?>
      <th><?php _e('Nombre', 'wp-events-registration'); ?></th>
      <th><?php _e('Apellidos', 'wp-events-registration'); ?></th>
      <th><?php _e('FIDE ID', 'wp-events-registration'); ?></th>
      <th><?php _e('Teléfono', 'wp-events-registration'); ?></th>
      <th><?php _e('Email', 'wp-events-registration'); ?></th>
      <th><?php _e('Alojamiento', 'wp-events-registration'); ?></th>
      <th><?php _e('Fecha', 'wp-events-registration'); ?></th>
      <th><?php _e('Acciones', 'wp-events-registration'); ?></th>
    </tr></thead>
    <tbody>
    <?php if ( empty( $inscripciones ) ) : ?>
      <tr><td colspan="9" style="text-align:center;padding:2rem;"><?php _e('No hay inscripciones.', 'wp-events-registration'); ?></td></tr>
    <?php else : ?>
      <?php foreach ( $inscripciones as $ins ) : ?>
        <tr>
          <?php if ( ! $evento_id ) : ?>
            <td>
              <a href="<?php echo admin_url('admin.php?page=wper-inscripciones&evento_id='.$ins->evento_id); ?>">
                <?php echo esc_html( $ins->evento_nombre ?? '—' ); ?>
              </a>
            </td>
          <?php endif; ?>
          <td><?php echo esc_html($ins->nombre); ?></td>
          <td><?php echo esc_html($ins->apellidos); ?></td>
          <td><?php echo esc_html($ins->fide_id ?: '—'); ?></td>
          <td><?php echo esc_html($ins->telefono ?: '—'); ?></td>
          <td><?php echo esc_html($ins->email ?: '—'); ?></td>
          <td><?php echo $ins->alojamiento ? '✅ Sí' : '❌ No'; ?></td>
          <td><?php echo esc_html(date_i18n('d/m/Y H:i', strtotime($ins->created_at))); ?></td>
          <td>
            <a href="<?php echo wp_nonce_url(
                admin_url('admin-post.php?action=wper_delete_inscripcion&id='.$ins->id.'&evento_id='.$ins->evento_id),
                'wper_delete_inscripcion_'.$ins->id
              ); ?>"
              class="button button-small button-link-delete"
              onclick="return confirm('<?php _e('¿Eliminar esta inscripción?', 'wp-events-registration'); ?>')">
              🗑️ <?php _e('Eliminar', 'wp-events-registration'); ?>
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>
