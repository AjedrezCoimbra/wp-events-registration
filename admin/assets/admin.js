jQuery(function($) {
    // Copiar shortcode al portapapeles
    $(document).on('click', '.dp-shortcode-copy, .dp-copy-btn', function() {
        var sc = $(this).data('shortcode') || $(this).closest('.dp-shortcode-box').find('.dp-shortcode-copy').data('shortcode');
        if (!sc) return;
        navigator.clipboard.writeText(sc).then(function() {
            // Reemplazamos alert por algo más sutil si fuera necesario
            alert('Shortcode copiado: ' + sc);
        }).catch(function() {
            prompt('Copia este shortcode:', sc);
        });
    });

    // WP Media Library Upload
    var frame;
    $(document).on('click', '.dp-media-upload-btn', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var targetId = $btn.data('target');
        var previewId = $btn.data('preview');

        if (frame) {
            frame.open();
            return;
        }

        frame = wp.media({
            title: 'Seleccionar imagen para el cartel',
            button: { text: 'Usar esta imagen' },
            multiple: false
        });

        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $('#' + targetId).val(attachment.url);
            $('#' + previewId).html('<img src="' + attachment.url + '" style="max-width:200px; display:block; border:1px solid #ccc; padding:5px; border-radius:4px;">');
            $btn.siblings('.dp-media-remove-btn').removeClass('hidden').show();
        });

        frame.open();
    });

    $(document).on('click', '.dp-media-remove-btn', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var targetId = $btn.data('target');
        var previewId = $btn.data('preview');

        $('#' + targetId).val('');
        $('#' + previewId).empty();
        $btn.addClass('hidden').hide();
    });
});
