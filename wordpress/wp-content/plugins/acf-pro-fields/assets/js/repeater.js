jQuery(function($) {
    $('.acf-pro-repeater').each(function() {
        const $repeater = $(this);
        const $rows = $repeater.find('.acf-pro-repeater-rows');
        const rowTemplate = $rows.find('.acf-pro-repeater-row').first().clone();

        $repeater.on('click', '.acf-pro-repeater-add-row', function() {
            const $newRow = rowTemplate.clone();
            const rowCount = $rows.find('.acf-pro-repeater-row').length;
            const fieldName = $repeater.data('field-name');

            $newRow.find('input, select, textarea').each(function() {
                const $input = $(this);
                const name = $input.attr('name').replace(/\[\d+\]/, `[${rowCount}]`);
                $input.attr('name', name).val('');
                if ($input.is('input[type=checkbox], input[type=radio]')) {
                    $input.prop('checked', false);
                } else if ($input.is('select')) {
                    $input.prop('selectedIndex', 0);
                }
            });

            $newRow.find('.acf-pro-repeater-row-title').text(`Row ${rowCount + 1}`);
            $rows.append($newRow);
        });

        $repeater.on('click', '.acf-pro-repeater-remove-row', function() {
            if (confirm(acfProL10n.confirmRemove)) {
                $(this).closest('.acf-pro-repeater-row').remove();
            }
        });
    });
});