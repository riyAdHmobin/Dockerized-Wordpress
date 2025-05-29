jQuery(function($) {
    $('.acf-pro-file-field').each(function() {
        const $field = $(this);
        const $input = $field.find('input[type=hidden]');
        const $preview = $field.find('.acf-pro-file-preview');

        $field.on('click', '.acf-pro-select-file', function(e) {
            e.preventDefault();
            const frame = acfPro.initMediaFrame({
                title: acfProL10n.selectFile,
                buttonText: acfProL10n.selectFile,
                multiple: false,
            });

            frame.on('select', function() {
                const attachment = frame.state().get('selection').first().toJSON();
                $input.val(attachment.id);
                $preview.html(
                    `<a href="${attachment.url}" target="_blank">${attachment.title}</a><br>` +
                    `<button type="button" class="button acf-pro-remove-file" aria-label="${acfProL10n.confirmRemove}">${acfProL10n.confirmRemove}</button>`
                ).show();
            });

            frame.open();
        });

        $field.on('click', '.acf-pro-remove-file', function(e) {
            e.preventDefault();
            $input.val('');
            $preview.hide();
        });
    });
});