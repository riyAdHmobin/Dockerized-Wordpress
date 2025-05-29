jQuery(function($) {
    $('.acf-pro-date-picker').each(function() {
        $(this).datepicker({
            dateFormat: $(this).data('format') || 'yy-mm-dd'
        });
    });
});