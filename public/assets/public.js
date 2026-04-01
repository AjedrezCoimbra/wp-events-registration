jQuery(function ($) {

    // ── Abrir modal de inscripción desde el calendario ──────
    $(document).on('click', '.wper-open-inscripcion-modal', function (e) {
        e.preventDefault();
        var targetId = $(this).data('target');
        var $hiddenForm = $(targetId);
        var $modal = $('#wper-modal-inscripcion');

        if ($hiddenForm.length) {
            $modal.find('.wper-modal-body').html($hiddenForm.html());
            $modal.css('display', 'flex').hide().fadeIn(200);
            $('body').css('overflow', 'hidden');
        }
    });

    // ── Modal de observaciones ──────
    $(document).on('click', '.wper-btn-obs-more', function () {
        var $modal = $('#wper-modal-obs');
        var title = $(this).data('title');
        var content = $(this).data('content');

        $modal.find('.wper-modal-header h3').text(title);
        $modal.find('.wper-modal-body').html(content);
        $modal.css('display', 'flex');
        $('body').css('overflow', 'hidden');
    });

    // ── Modal de inscritos ──────
    $(document).on('click', '.wper-open-inscritos-modal', function () {
        var $modal = $('#wper-modal-obs'); // Reutilizamos el modal de observaciones
        var eventoId = $(this).data('evento-id');
        var nombre   = $(this).data('evento-nombre');

        $modal.find('.wper-modal-header h3').text(wperData.i18n.inscritos_en + ': ' + nombre);
        $modal.find('.wper-modal-body').html('<p>' + wperData.i18n.cargando + '</p>');
        $modal.css('display', 'flex');
        $('body').css('overflow', 'hidden');

        $.post(wperData.ajax_url, {
            action: 'wper_get_inscritos',
            evento_id: eventoId
        }, function(response) {
            if (response.success) {
                $modal.find('.wper-modal-body').html(response.data.html);
            } else {
                $modal.find('.wper-modal-body').html('<p>' + (response.data.message || 'Error') + '</p>');
            }
        });
    });

    $(document).on('click', '.wper-modal-close, .wper-modal', function (e) {
        if (e.target !== this && !$(e.target).hasClass('wper-modal-close')) return;
        $('.wper-modal').hide();
        $('body').css('overflow', 'auto');
    });

    // ── Envío del formulario de inscripción vía AJAX ──────
    $(document).on('submit', '.wper-form-inscripcion', function (e) {
        e.preventDefault();

        var $form = $(this);
        var eventoId = $form.data('evento-id');
        var $msg = $form.find('.wper-form-msg');
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
                        .html('<strong>👍</strong> ' + response.data.message)
                        .fadeIn();
                    $form[0].reset();
                    // Ocultar tras inscripción exitosa tras un tiempo prudencial (6s)
                    setTimeout(function () {
                        var $modal = $form.closest('.wper-modal');
                        if($modal.length) {
                             $modal.fadeOut(300, function(){ $('body').css('overflow', 'auto'); });
                        } else {
                             $form.closest('.wper-inscripcion-hidden-form').slideUp(300);
                        }
                    }, 6000);
                } else {
                    $msg.addClass('wper-aviso wper-aviso-error')
                        .html('<strong>⚠️</strong> ' + (response.data.message || wperData.i18n.error_gen))
                        .fadeIn();
                }
            })
            .fail(function () {
                $msg.addClass('wper-aviso wper-aviso-error')
                    .text(wperData.i18n.error_gen)
                    .fadeIn();
            })
            .always(function () {
                $btn.prop('disabled', false).text(btnText);
            });
    });

});

