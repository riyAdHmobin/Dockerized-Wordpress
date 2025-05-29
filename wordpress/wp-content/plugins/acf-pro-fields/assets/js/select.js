jQuery(function($) {
    $('.acf-pro-select-field select').each(function() {
        const $select = $(this);
        if ($select.attr('multiple')) {
            $select.css('width', '100%').select2();
        }
    });
});