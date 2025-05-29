jQuery(function($) {
    $('.acf-pro-gallery').each(function() {
        const $gallery = $(this);
        const $input = $gallery.find('input[type=hidden]');
        const $thumbnails = $gallery.find('.acf-pro-gallery-thumbnails');

        $gallery.on('click', '.acf-pro-gallery-add', function(e) {
            e.preventDefault();
            const frame = acfPro.initMediaFrame({
                title: acfProL10n.selectImages,
                buttonText: acfProL10n.addToGallery,
                multiple: true,
            });

            frame.on('select', function() {
                const selection = frame.state().get('selection');
                let ids = $input.val() ? $input.val().split(',') : [];

                selection.each(function(attachment) {
                    const attachmentId = attachment.id;
                    if (ids.indexOf(attachmentId.toString()) === -1) {
                        ids.push(attachmentId);
                        const imageUrl = attachment.attributes.sizes.thumbnail ? attachment.attributes.sizes.thumbnail.url : attachment.attributes.url;
                        $thumbnails.append(`
                            <div class="acf-pro-gallery-thumbnail" data-id="${attachmentId}">
                                <img src="${imageUrl}" alt="${attachment.attributes.title}">
                                <button type="button" class="button-link acf-pro-gallery-remove" aria-label="${acfProL10n.confirmRemove}">×</button>
                            </div>
                        `);
                    }
                });

                $input.val(ids.join(','));
            });

            frame.open();
        });

        $gallery.on('click', '.acf-pro-gallery-remove', function(e) {
            e.preventDefault();
            const $thumbnail = $(this).closest('.acf-pro-gallery-thumbnail');
            const id = $thumbnail.data('id');
            let ids = $input.val().split(',').filter(item => item !== id.toString());
            $input.val(ids.join(','));
            $thumbnail.remove();
        });
    });
});