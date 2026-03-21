jQuery(function ($) {

    // ── Toggle formulario inline desde el calendario ──────
    $(document).on('click', '.wper-btn-primary[href^="#wper-form-"]', function (e) {
        e.preventDefault();
        var target = $(this).attr('href');
        var $form = $(target);
        if ($form.length) {
            $form.slideToggle(200);
            $('html, body').animate({ scrollTop: $form.offset().top - 80 }, 300);
        }
    });

    // ── Envío del formulario de inscripción vía AJAX ──────
    $(document).on('submit', '.wper-form-inscripcion', function (e) {
        e.preventDefault();

        var $form = $(this);
        var eventoId = $form.data('evento-id');
        var $msg = $('#wper-msg-' + eventoId);
        var $btn = $form.find('button[type="submit"]');
        var btnText = $btn.text();

        // Deshabilitar botón
        $btn.prop('disabled', true).text(wperData.i18n.enviando);
        $msg.hide().removeClass('wper-aviso-ok wper-aviso-error');

        var data = {
            action: 'wper_inscribir',
            nonce: wperData.nonce,
            evento_id: eventoId,
            nombre: $form.find('[name="nombre"]').val(),
            apellidos: $form.find('[name="apellidos"]').val(),
            fide_id: $form.find('[name="fide_id"]').val(),
            telefono: $form.find('[name="telefono"]').val(),
            email: $form.find('[name="email"]').val(),
            alojamiento: $form.find('[name="alojamiento"]').is(':checked') ? 1 : 0,
            observaciones: $form.find('[name="observaciones"]').val(),
        };

        $.post(wperData.ajax_url, data)
            .done(function (response) {
                if (response.success) {
                    $msg.addClass('wper-aviso wper-aviso-ok')
                        .text(response.data.message)
                        .show();
                    $form[0].reset();
                    // Ocultar el formulario tras inscripción exitosa
                    setTimeout(function () {
                        $form.closest('.wper-cal-form-inline').slideUp(300);
                    }, 3000);
                } else {
                    $msg.addClass('wper-aviso wper-aviso-error')
                        .text(response.data.message || wperData.i18n.error_gen)
                        .show();
                }
            })
            .fail(function () {
                $msg.addClass('wper-aviso wper-aviso-error')
                    .text(wperData.i18n.error_gen)
                    .show();
            })
            .always(function () {
                $btn.prop('disabled', false).text(btnText);
            });
    });

});
