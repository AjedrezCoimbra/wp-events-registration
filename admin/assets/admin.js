jQuery(function($) {
    // Copiar shortcode al portapapeles
    $(document).on('click', '.wper-shortcode-copy, .wper-copy-btn', function() {
        var sc = $(this).data('shortcode') || $(this).closest('.wper-shortcode-box').find('.wper-shortcode-copy').data('shortcode');
        if (!sc) return;
        navigator.clipboard.writeText(sc).then(function() {
            alert(wperAdminData.i18n.shortcode_copiado + sc);
        }).catch(function() {
            prompt(wperAdminData.i18n.copia_shortcode, sc);
        });
    });

    // WP Media Library Upload
    var frame;
    $(document).on('click', '.wper-media-upload-btn', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var targetId = $btn.data('target');
        var previewId = $btn.data('preview');

        if (frame) {
            frame.open();
            return;
        }

        frame = wp.media({
            title: wperAdminData.i18n.seleccionar_img,
            button: { text: wperAdminData.i18n.usar_imagen },
            multiple: false
        });

        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $('#' + targetId).val(attachment.url);
            $('#' + previewId).html('<img src="' + attachment.url + '" style="max-width:200px; display:block; border:1px solid #ccc; padding:5px; border-radius:4px;">');
            $btn.siblings('.wper-media-remove-btn').removeClass('hidden').show();
        });

        frame.open();
    });

    $(document).on('click', '.wper-media-remove-btn', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var targetId = $btn.data('target');
        var previewId = $btn.data('preview');

        $('#' + targetId).val('');
        $('#' + previewId).empty();
        $btn.addClass('hidden').hide();
    });
});
