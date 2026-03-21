jQuery(function($) {
    // Copiar shortcode al portapapeles
    $(document).on('click', '.dp-shortcode-copy, .dp-copy-btn', function() {
        var sc = $(this).data('shortcode') || $(this).closest('.dp-shortcode-box').find('.dp-shortcode-copy').data('shortcode');
        if (!sc) return;
        navigator.clipboard.writeText(sc).then(function() {
            alert('Shortcode copiado: ' + sc);
        }).catch(function() {
            prompt('Copia este shortcode:', sc);
        });
    });
});
