jQuery(function ($) {

    // ── Toggle formulario inline desde el calendario ──────
    $(document).on('click', '.dp-btn-primary[href^="#dp-form-"]', function (e) {
        e.preventDefault();
        var target = $(this).attr('href');
        var $form  = $(target);
        if ($form.length) {
            $form.slideToggle(200);
            $('html, body').animate({ scrollTop: $form.offset().top - 80 }, 300);
        }
    });

    // ── Envío del formulario de inscripción vía AJAX ──────
    $(document).on('submit', '.dp-form-inscripcion', function (e) {
        e.preventDefault();

        var $form     = $(this);
        var eventoId  = $form.data('evento-id');
        var $msg      = $('#dp-msg-' + eventoId);
        var $btn      = $form.find('button[type="submit"]');
        var btnText   = $btn.text();

        // Deshabilitar botón
        $btn.prop('disabled', true).text(dpTorneos.i18n.enviando);
        $msg.hide().removeClass('dp-aviso-ok dp-aviso-error');

        var data = {
            action:      'dp_inscribir',
            nonce:       dpTorneos.nonce,
            evento_id:   eventoId,
            nombre:      $form.find('[name="nombre"]').val(),
            apellidos:   $form.find('[name="apellidos"]').val(),
            fide_id:     $form.find('[name="fide_id"]').val(),
            telefono:    $form.find('[name="telefono"]').val(),
            email:       $form.find('[name="email"]').val(),
            alojamiento: $form.find('[name="alojamiento"]').is(':checked') ? 1 : 0,
        };

        $.post(dpTorneos.ajax_url, data)
            .done(function (response) {
                if (response.success) {
                    $msg.addClass('dp-aviso dp-aviso-ok')
                        .text(response.data.message)
                        .show();
                    $form[0].reset();
                    // Ocultar el formulario tras inscripción exitosa
                    setTimeout(function () {
                        $form.closest('.dp-cal-form-inline').slideUp(300);
                    }, 3000);
                } else {
                    $msg.addClass('dp-aviso dp-aviso-error')
                        .text(response.data.message || dpTorneos.i18n.error_gen)
                        .show();
                }
            })
            .fail(function () {
                $msg.addClass('dp-aviso dp-aviso-error')
                    .text(dpTorneos.i18n.error_gen)
                    .show();
            })
            .always(function () {
                $btn.prop('disabled', false).text(btnText);
            });
    });

});
