<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap dp-torneos-wrap">
  <h1 class="wp-heading-inline">♟ <?php _e('Eventos', 'dp-torneos'); ?></h1>
  <a href="<?php echo admin_url('admin.php?page=dp-torneos-nuevo'); ?>" class="page-title-action">
    <?php _e('Añadir nuevo', 'dp-torneos'); ?>
  </a>

  <?php if ( $mensaje === 'creado' ) : ?>
    <div class="notice notice-success is-dismissible"><p><?php _e('✅ Evento creado correctamente.', 'dp-torneos'); ?></p></div>
  <?php elseif ( $mensaje === 'actualizado' ) : ?>
    <div class="notice notice-success is-dismissible"><p><?php _e('✅ Evento actualizado correctamente.', 'dp-torneos'); ?></p></div>
  <?php elseif ( $mensaje === 'eliminado' ) : ?>
    <div class="notice notice-success is-dismissible"><p><?php _e('🗑️ Evento eliminado.', 'dp-torneos'); ?></p></div>
  <?php endif; ?>

  <!-- Filtros -->
  <ul class="subsubsub">
    <li><a href="<?php echo admin_url('admin.php?page=dp-torneos-eventos'); ?>" <?php echo !$estado_filtro ? 'class="current"' : ''; ?>><?php _e('Todos', 'dp-torneos'); ?></a> |</li>
    <li><a href="<?php echo admin_url('admin.php?page=dp-torneos-eventos&estado=abierto'); ?>" <?php echo $estado_filtro==='abierto' ? 'class="current"' : ''; ?>><?php _e('Abiertos', 'dp-torneos'); ?></a> |</li>
    <li><a href="<?php echo admin_url('admin.php?page=dp-torneos-eventos&estado=cerrado'); ?>" <?php echo $estado_filtro==='cerrado' ? 'class="current"' : ''; ?>><?php _e('Cerrados', 'dp-torneos'); ?></a> |</li>
    <li><a href="<?php echo admin_url('admin.php?page=dp-torneos-eventos&estado=borrador'); ?>" <?php echo $estado_filtro==='borrador' ? 'class="current"' : ''; ?>><?php _e('Borradores', 'dp-torneos'); ?></a></li>
  </ul>

  <table class="wp-list-table widefat fixed striped dp-table">
    <thead><tr>
      <th style="width:25%"><?php _e('Nombre', 'dp-torneos'); ?></th>
      <th><?php _e('Modalidad', 'dp-torneos'); ?></th>
      <th><?php _e('Lugar', 'dp-torneos'); ?></th>
      <th><?php _e('Inicio torneo', 'dp-torneos'); ?></th>
      <th><?php _e('Fin inscripción', 'dp-torneos'); ?></th>
      <th><?php _e('Estado', 'dp-torneos'); ?></th>
      <th><?php _e('Inscritos', 'dp-torneos'); ?></th>
      <th><?php _e('Acciones', 'dp-torneos'); ?></th>
    </tr></thead>
    <tbody>
    <?php if ( empty( $eventos ) ) : ?>
      <tr><td colspan="8" style="text-align:center;padding:2rem;"><?php _e('No hay eventos.', 'dp-torneos'); ?></td></tr>
    <?php else : ?>
      <?php foreach ( $eventos as $ev ) :
        $n_inscritos = DP_Torneos_DB::count_inscripciones( $ev->id );
        $estado_class = array('borrador'=>'dp-badge-borrador','abierto'=>'dp-badge-abierto','cerrado'=>'dp-badge-cerrado')[$ev->estado] ?? '';
      ?>
      <tr>
        <td>
          <strong><?php echo esc_html($ev->nombre); ?></strong><br>
          <small class="dp-shortcode-copy" title="<?php _e('Copiar shortcode','dp-torneos'); ?>"
            data-shortcode="[dp_torneo_inscripcion id=&quot;<?php echo $ev->id; ?>&quot;]">
            📋 [dp_torneo_inscripcion id="<?php echo $ev->id; ?>"]
          </small>
        </td>
        <td><?php echo esc_html($ev->modalidad); ?></td>
        <td><?php echo esc_html($ev->poblacion.', '.$ev->provincia); ?></td>
        <td><?php echo esc_html(date_i18n('d/m/Y', strtotime($ev->fecha_inicio))); ?></td>
        <td><?php echo esc_html(date_i18n('d/m/Y', strtotime($ev->fecha_fin_inscripcion))); ?></td>
        <td><span class="dp-badge <?php echo $estado_class; ?>"><?php echo esc_html(ucfirst($ev->estado)); ?></span></td>
        <td>
          <a href="<?php echo admin_url('admin.php?page=dp-torneos-inscripciones&evento_id='.$ev->id); ?>">
            <?php echo $n_inscritos; ?> <?php _e('inscritos', 'dp-torneos'); ?>
          </a>
        </td>
        <td class="dp-actions">
          <a href="<?php echo admin_url('admin.php?page=dp-torneos-nuevo&id='.$ev->id); ?>" class="button button-small">✏️ <?php _e('Editar', 'dp-torneos'); ?></a>
          <a href="<?php echo admin_url('admin.php?page=dp-torneos-inscripciones&evento_id='.$ev->id); ?>" class="button button-small">👥 <?php _e('Inscritos', 'dp-torneos'); ?></a>
          <a href="<?php echo wp_nonce_url( admin_url('admin-post.php?action=dp_export_pdf&evento_id='.$ev->id), 'dp_export_pdf_'.$ev->id ); ?>" class="button button-small" target="_blank">📄 PDF</a>
          <a href="<?php echo wp_nonce_url( admin_url('admin-post.php?action=dp_export_csv&evento_id='.$ev->id), 'dp_export_csv_'.$ev->id ); ?>" class="button button-small">📊 CSV</a>
          <a href="<?php echo wp_nonce_url( admin_url('admin-post.php?action=dp_delete_evento&id='.$ev->id), 'dp_delete_evento_'.$ev->id ); ?>"
            class="button button-small button-link-delete"
            onclick="return confirm('<?php _e('¿Eliminar este evento y todas sus inscripciones?', 'dp-torneos'); ?>')">
            🗑️ <?php _e('Eliminar', 'dp-torneos'); ?>
          </a>
        </td>
      </tr>
      <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
  </table>

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
