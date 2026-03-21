<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap wper-wrap">
  <h1 class="wp-heading-inline">♟ <?php _e('Eventos', 'wp-events-registration'); ?></h1>
  <a href="<?php echo admin_url('admin.php?page=wper-nuevo'); ?>" class="page-title-action">
    <?php _e('Añadir nuevo', 'wp-events-registration'); ?>
  </a>

  <?php if ( $mensaje === 'creado' ) : ?>
    <div class="notice notice-success is-dismissible"><p><?php _e('✅ Evento creado correctamente.', 'wp-events-registration'); ?></p></div>
  <?php elseif ( $mensaje === 'actualizado' ) : ?>
    <div class="notice notice-success is-dismissible"><p><?php _e('✅ Evento actualizado correctamente.', 'wp-events-registration'); ?></p></div>
  <?php elseif ( $mensaje === 'eliminado' ) : ?>
    <div class="notice notice-success is-dismissible"><p><?php _e('🗑️ Evento eliminado.', 'wp-events-registration'); ?></p></div>
  <?php endif; ?>

  <!-- Filtros -->
  <ul class="subsubsub">
    <li><a href="<?php echo admin_url('admin.php?page=wper-eventos'); ?>" <?php echo !$estado_filtro ? 'class="current"' : ''; ?>><?php _e('Todos', 'wp-events-registration'); ?></a> |</li>
    <li><a href="<?php echo admin_url('admin.php?page=wper-eventos&estado=abierto'); ?>" <?php echo $estado_filtro==='abierto' ? 'class="current"' : ''; ?>><?php _e('Abiertos', 'wp-events-registration'); ?></a> |</li>
    <li><a href="<?php echo admin_url('admin.php?page=wper-eventos&estado=cerrado'); ?>" <?php echo $estado_filtro==='cerrado' ? 'class="current"' : ''; ?>><?php _e('Cerrados', 'wp-events-registration'); ?></a> |</li>
    <li><a href="<?php echo admin_url('admin.php?page=wper-eventos&estado=borrador'); ?>" <?php echo $estado_filtro==='borrador' ? 'class="current"' : ''; ?>><?php _e('Borradores', 'wp-events-registration'); ?></a></li>
  </ul>

  <div class="wper-table-wrap">
  <table class="wp-list-table widefat fixed striped wper-table">
    <thead><tr>
      <th style="width:25%"><?php _e('Nombre', 'wp-events-registration'); ?></th>
      <th><?php _e('Modalidad', 'wp-events-registration'); ?></th>
      <th><?php _e('Lugar', 'wp-events-registration'); ?></th>
      <th><?php _e('Inicio evento', 'wp-events-registration'); ?></th>
      <th><?php _e('Fin inscripción', 'wp-events-registration'); ?></th>
      <th><?php _e('Estado', 'wp-events-registration'); ?></th>
      <th><?php _e('Inscritos', 'wp-events-registration'); ?></th>
      <th><?php _e('Acciones', 'wp-events-registration'); ?></th>
    </tr></thead>
    <tbody>
    <?php if ( empty( $eventos ) ) : ?>
      <tr><td colspan="8" style="text-align:center;padding:2rem;"><?php _e('No hay eventos.', 'wp-events-registration'); ?></td></tr>
    <?php else : ?>
      <?php foreach ( $eventos as $ev ) :
        $n_inscritos = WPER_DB::count_inscripciones( $ev->id );
        $estado_class = array('borrador'=>'wper-badge-borrador','abierto'=>'wper-badge-abierto','cerrado'=>'wper-badge-cerrado')[$ev->estado] ?? '';
      ?>
      <tr>
        <td>
          <strong><?php echo esc_html($ev->nombre); ?></strong><br>
          <small class="wper-shortcode-copy" title="<?php _e('Copiar shortcode','wp-events-registration'); ?>"
            data-shortcode="[wper_inscripcion id=&quot;<?php echo $ev->id; ?>&quot;]">
            📋 [wper_inscripcion id="<?php echo $ev->id; ?>"]
          </small>
        </td>
        <td><?php echo esc_html($ev->modalidad); ?></td>
        <td><?php echo esc_html($ev->poblacion.', '.$ev->provincia); ?></td>
        <td><?php echo esc_html(date_i18n('d/m/Y', strtotime($ev->fecha_inicio))); ?></td>
        <td><?php echo esc_html(date_i18n('d/m/Y', strtotime($ev->fecha_fin_inscripcion))); ?></td>
        <td><span class="wper-badge <?php echo $estado_class; ?>"><?php echo esc_html(ucfirst($ev->estado)); ?></span></td>
        <td>
          <a href="<?php echo admin_url('admin.php?page=wper-inscripciones&evento_id='.$ev->id); ?>">
            <?php echo $n_inscritos; ?> <?php _e('inscritos', 'wp-events-registration'); ?>
          </a>
        </td>
        <td class="wper-actions">
          <a href="<?php echo admin_url('admin.php?page=wper-nuevo&id='.$ev->id); ?>" class="button button-small">✏️ <?php _e('Editar', 'wp-events-registration'); ?></a>
          <a href="<?php echo admin_url('admin.php?page=wper-inscripciones&evento_id='.$ev->id); ?>" class="button button-small">👥 <?php _e('Inscritos', 'wp-events-registration'); ?></a>
          <a href="<?php echo wp_nonce_url( admin_url('admin-post.php?action=wper_export_pdf&evento_id='.$ev->id), 'wper_export_pdf_'.$ev->id ); ?>" class="button button-small" target="_blank">📄 PDF</a>
          <a href="<?php echo wp_nonce_url( admin_url('admin-post.php?action=wper_export_csv&evento_id='.$ev->id), 'wper_export_csv_'.$ev->id ); ?>" class="button button-small">📊 CSV</a>
          <a href="<?php echo wp_nonce_url( admin_url('admin-post.php?action=wper_delete_evento&id='.$ev->id), 'wper_delete_evento_'.$ev->id ); ?>"
            class="button button-small button-link-delete"
            onclick="return confirm('<?php _e('¿Eliminar este evento y todas sus inscripciones?', 'wp-events-registration'); ?>')">
            🗑️ <?php _e('Eliminar', 'wp-events-registration'); ?>
          </a>
        </td>
      </tr>
      <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
  </table>
  </div>

  <?php if ( $total_pages > 1 ) : ?>
    <div class="tablenav bottom">
      <div class="tablenav-pages">
        <?php
        echo paginate_links( array(
            'base'      => add_query_arg( 'paged', '%#%' ),
            'format'    => '',
            'current'   => $paged,
            'total'     => $total_pages,
        ) );
        ?>
      </div>
    </div>
  <?php endif; ?>

</div>
