<?php if ( ! defined( 'ABSPATH' ) ) exit;
$editing = ! is_null( $evento );
$title   = $editing ? __('Editar Evento', 'wp-events-registration') : __('Nuevo Evento', 'wp-events-registration');
?>
<div class="wrap wper-wrap">
  <h1>♟ <?php echo esc_html($title); ?></h1>

  <?php if ( $error === 'campos_obligatorios' ) : ?>
    <div class="notice notice-error"><p><?php _e('⚠️ Por favor, rellena todos los campos obligatorios.', 'wp-events-registration'); ?></p></div>
  <?php endif; ?>

  <?php if ( $editing ) : ?>
    <div class="wper-shortcode-box">
      <strong><?php _e('Shortcode de inscripción:', 'wp-events-registration'); ?></strong>
      <code class="wper-shortcode-copy" data-shortcode="[wper_inscripcion id=&quot;<?php echo $evento->id; ?>&quot;]">
        [wper_inscripcion id="<?php echo $evento->id; ?>"]
      </code>
      <button type="button" class="button button-small wper-copy-btn" data-target=".wper-shortcode-copy">📋 <?php _e('Copiar', 'wp-events-registration'); ?></button>
      <br><small><?php _e('Pega este shortcode en cualquier página para mostrar el formulario de inscripción.', 'wp-events-registration'); ?></small>
    </div>
  <?php endif; ?>

  <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
    <input type="hidden" name="action"    value="wper_save_evento">
    <input type="hidden" name="evento_id" value="<?php echo $editing ? $evento->id : 0; ?>">
    <?php wp_nonce_field('wper_save_evento'); ?>

    <table class="form-table wper-form-table">

      <tr>
        <th><label for="nombre"><?php _e('Nombre del evento', 'wp-events-registration'); ?> <span class="required">*</span></label></th>
        <td><input type="text" id="nombre" name="nombre" class="regular-text" required
              value="<?php echo $editing ? esc_attr($evento->nombre) : ''; ?>"></td>
      </tr>

      <tr>
        <th><label for="modalidad"><?php _e('Modalidad', 'wp-events-registration'); ?> <span class="required">*</span></label></th>
        <td>
          <select id="modalidad" name="modalidad" required>
            <option value="Individual"   <?php selected($editing ? $evento->modalidad : '', 'Individual'); ?>><?php _e('Individual', 'wp-events-registration'); ?></option>
            <option value="Por Equipos"  <?php selected($editing ? $evento->modalidad : '', 'Por Equipos'); ?>><?php _e('Por Equipos', 'wp-events-registration'); ?></option>
          </select>
        </td>
      </tr>

      <tr>
        <th><label for="estado"><?php _e('Estado', 'wp-events-registration'); ?> <span class="required">*</span></label></th>
        <td>
          <select id="estado" name="estado" required>
            <option value="borrador" <?php selected($editing ? $evento->estado : 'borrador', 'borrador'); ?>><?php _e('Borrador', 'wp-events-registration'); ?></option>
            <option value="abierto"  <?php selected($editing ? $evento->estado : '', 'abierto'); ?>><?php _e('Abierto', 'wp-events-registration'); ?></option>
            <option value="cerrado"  <?php selected($editing ? $evento->estado : '', 'cerrado'); ?>><?php _e('Cerrado', 'wp-events-registration'); ?></option>
          </select>
          <p class="description"><?php _e('Solo los eventos "Abiertos" son visibles en el calendario público.', 'wp-events-registration'); ?></p>
        </td>
      </tr>

      <tr>
        <th><label for="poblacion"><?php _e('Población', 'wp-events-registration'); ?> <span class="required">*</span></label></th>
        <td><input type="text" id="poblacion" name="poblacion" class="regular-text" required
              value="<?php echo $editing ? esc_attr($evento->poblacion) : ''; ?>"></td>
      </tr>

      <tr>
        <th><label for="provincia"><?php _e('Provincia', 'wp-events-registration'); ?> <span class="required">*</span></label></th>
        <td><input type="text" id="provincia" name="provincia" class="regular-text" required
              value="<?php echo $editing ? esc_attr($evento->provincia) : ''; ?>"></td>
      </tr>

      <tr>
        <th><label for="numero_rondas"><?php _e('Número de rondas', 'wp-events-registration'); ?></label></th>
        <td><input type="number" id="numero_rondas" name="numero_rondas" min="1" max="30" class="small-text"
              value="<?php echo $editing ? esc_attr($evento->numero_rondas) : ''; ?>"></td>
      </tr>

      <tr>
        <th><label for="tiempo_juego"><?php _e('Tiempo de juego', 'wp-events-registration'); ?></label></th>
        <td><input type="text" id="tiempo_juego" name="tiempo_juego" maxlength="30" class="regular-text"
               value="<?php echo $editing ? esc_attr($evento->tiempo_juego) : ''; ?>"
               placeholder="ej: 90' + 30''"></td>
      </tr>

      <tr>
        <th><label for="elo_fide"><?php _e('Valedero para ELO FIDE', 'wp-events-registration'); ?></label></th>
        <td>
          <input type="checkbox" id="elo_fide" name="elo_fide" value="1" <?php checked($editing ? $evento->elo_fide : 0, 1); ?>>
          <?php _e('Sí', 'wp-events-registration'); ?>
        </td>
      </tr>

      <tr>
        <th><label for="ritmo_juego"><?php _e('Ritmo de juego', 'wp-events-registration'); ?></label></th>
        <td>
          <select id="ritmo_juego" name="ritmo_juego">
            <option value=""><?php _e('— Sin especificar —', 'wp-events-registration'); ?></option>
            <option value="Clásico" <?php selected($editing ? $evento->ritmo_juego : '', 'Clásico'); ?>><?php _e('Clásico', 'wp-events-registration'); ?></option>
            <option value="Rápido"  <?php selected($editing ? $evento->ritmo_juego : '', 'Rápido'); ?>><?php _e('Rápido', 'wp-events-registration'); ?></option>
            <option value="Blitz"   <?php selected($editing ? $evento->ritmo_juego : '', 'Blitz'); ?>><?php _e('Blitz', 'wp-events-registration'); ?></option>
          </select>
        </td>
      </tr>

      <tr>
        <th><label for="cuota_inscripcion"><?php _e('Cuota de inscripción (€)', 'wp-events-registration'); ?></label></th>
        <td>
          <input type="number" id="cuota_inscripcion" name="cuota_inscripcion" min="0" step="0.01" class="small-text"
              value="<?php echo $editing ? esc_attr($evento->cuota_inscripcion) : ''; ?>">
          <p class="description"><?php _e('Deja vacío si es gratuito.', 'wp-events-registration'); ?></p>
        </td>
      </tr>

      <tr><td colspan="2"><hr></td></tr>

      <tr>
        <th><label for="fecha_inicio"><?php _e('Inicio del evento', 'wp-events-registration'); ?> <span class="required">*</span></label></th>
        <td><input type="date" id="fecha_inicio" name="fecha_inicio" required
              value="<?php echo $editing ? esc_attr($evento->fecha_inicio) : ''; ?>"></td>
      </tr>

      <tr>
        <th><label for="fecha_fin"><?php _e('Fin del evento', 'wp-events-registration'); ?> <span class="required">*</span></label></th>
        <td><input type="date" id="fecha_fin" name="fecha_fin" required
              value="<?php echo $editing ? esc_attr($evento->fecha_fin) : ''; ?>"></td>
      </tr>

      <tr>
        <th><label for="fecha_inicio_inscripcion"><?php _e('Inicio inscripción', 'wp-events-registration'); ?> <span class="required">*</span></label></th>
        <td><input type="date" id="fecha_inicio_inscripcion" name="fecha_inicio_inscripcion" required
              value="<?php echo $editing ? esc_attr($evento->fecha_inicio_inscripcion) : ''; ?>"></td>
      </tr>

      <tr>
        <th><label for="fecha_fin_inscripcion"><?php _e('Fin inscripción', 'wp-events-registration'); ?> <span class="required">*</span></label></th>
        <td><input type="date" id="fecha_fin_inscripcion" name="fecha_fin_inscripcion" required
              value="<?php echo $editing ? esc_attr($evento->fecha_fin_inscripcion) : ''; ?>"></td>
      </tr>

      <tr><td colspan="2"><hr></td></tr>

      <tr>
        <th><label for="url_bases"><?php _e('URL de las bases', 'wp-events-registration'); ?></label></th>
        <td><input type="url" id="url_bases" name="url_bases" class="large-text"
              value="<?php echo $editing ? esc_attr($evento->url_bases) : ''; ?>"
              placeholder="https://..."></td>
      </tr>

      <tr>
        <th><label for="google_maps"><?php _e('Google Maps (URL)', 'wp-events-registration'); ?></label></th>
        <td><input type="url" id="google_maps" name="google_maps" class="large-text"
              value="<?php echo $editing ? esc_attr($evento->google_maps) : ''; ?>"
              placeholder="https://maps.google.com/..."></td>
      </tr>

      <tr>
        <th><label for="cartel_url"><?php _e('Imagen del cartel', 'wp-events-registration'); ?></label></th>
        <td>
          <div class="wper-media-upload">
            <input type="hidden" id="cartel_url" name="cartel_url" value="<?php echo $editing ? esc_attr($evento->cartel_url) : ''; ?>">
            <div id="cartel-preview" class="wper-media-preview" style="margin-bottom:10px;">
              <?php if ( $editing && $evento->cartel_url ) : ?>
                <img src="<?php echo esc_url($evento->cartel_url); ?>" style="max-width:200px; display:block; border:1px solid #ccc; padding:5px; border-radius:4px;">
              <?php endif; ?>
            </div>
            <button type="button" class="button button-secondary wper-media-upload-btn" data-target="cartel_url" data-preview="cartel-preview"><?php _e('Seleccionar imagen', 'wp-events-registration'); ?></button>
            <button type="button" class="button button-link wper-media-remove-btn <?php echo ($editing && $evento->cartel_url) ? '' : 'hidden'; ?>" data-target="cartel_url" data-preview="cartel-preview" style="color:#d63638; text-decoration:none; vertical-align:middle;"><?php _e('Eliminar', 'wp-events-registration'); ?></button>
          </div>
          <p class="description"><?php _e('Sube el cartel del torneo (JPG, PNG, SVG).', 'wp-events-registration'); ?></p>
        </td>
      </tr>

      <tr>
        <th><label for="observaciones"><?php _e('Observaciones', 'wp-events-registration'); ?></label></th>
        <td>
          <?php 
          $obs_content = $editing ? $evento->observaciones : '';
          wp_editor( $obs_content, 'observaciones', array(
            'textarea_name' => 'observaciones',
            'media_buttons' => true,
            'textarea_rows' => 8,
            'teeny'         => false,
            'quicktags'     => true,
            'editor_class'  => 'large-text'
          ) ); 
          ?>
          <p class="description"><?php _e('Información detallada para mostrar en la ficha del evento.', 'wp-events-registration'); ?></p>
        </td>
      </tr>

    </table>

    <p class="submit">
      <button type="submit" class="button button-primary">
        <?php echo $editing ? __('💾 Actualizar evento', 'wp-events-registration') : __('➕ Crear evento', 'wp-events-registration'); ?>
      </button>
      <a href="<?php echo admin_url('admin.php?page=wper-eventos'); ?>" class="button">
        <?php _e('Cancelar', 'wp-events-registration'); ?>
      </a>
    </p>
  </form>
</div>
