jQuery(function($) {
    $('.acf-pro-flexible-content').each(function() {
        const $fc = $(this);
        const $rows = $fc.find('.acf-pro-fc-rows');
        const fieldName = $fc.data('field-name');
        const layouts = acfProLayouts;

        $fc.on('click', '.acf-pro-fc-add-row', function() {
            const layoutName = $fc.find('.acf-pro-fc-layout-select').val();
            if (!layoutName || !layouts[layoutName]) return;

            const layout = layouts[layoutName];
            const rowCount = $rows.find('.acf-pro-fc-row').length;

            const $newRow = $(`
                <div class="acf-pro-fc-row" data-layout="${layoutName}">
                    <div class="acf-pro-fc-row-header">
                        <span class="acf-pro-fc-row-title">${layout.label}</span>
                        <button type="button" class="button acf-pro-fc-remove-row" aria-label="${acfProL10n.confirmRemove}">
                            ${acfProL10n.confirmRemove}
                        </button>
                    </div>
                    <div class="acf-pro-fc-row-content"></div>
                </div>
            `);

            const $rowContent = $newRow.find('.acf-pro-fc-row-content');
            $.each(layout.sub_fields, function(i, subField) {
                let fieldHtml = `
                    <div class="acf-pro-fc-field">
                        <label>${subField.label}${subField.required ? ' <span class="required" aria-hidden="true">*</span>' : ''}</label>
                `;
                if (subField.instructions) {
                    fieldHtml += `<p class="description">${subField.instructions}</p>`;
                }

                const fieldName = `${fieldName}[${rowCount}][${subField.name}]`;
                switch (subField.type) {
                    case 'text':
                    case 'email':
                    case 'url':
                        fieldHtml += `<input type="${subField.type}" name="${fieldName}" class="regular-text">`;
                        break;
                    case 'textarea':
                        fieldHtml += `<textarea name="${fieldName}" rows="4" class="large-text"></textarea>`;
                        break;
                    case 'select':
                        fieldHtml += `<select name="${fieldName}${subField.multiple ? '[]' : ''}" ${subField.multiple ? 'multiple' : ''}>`;
                        if (!subField.multiple) {
                            fieldHtml += `<option value="">${acfProL10n.selectOption}</option>`;
                        }
                        $.each(subField.choices, function(key, label) {
                            fieldHtml += `<option value="${key}">${label}</option>`;
                        });
                        fieldHtml += `</select>`;
                        break;
                    case 'date_picker':
                        fieldHtml += `<input type="text" name="${fieldName}" class="acf-pro-date-picker" data-format="${subField.date_format || 'yy-mm-dd'}">`;
                        break;
                    default:
                        fieldHtml += `<input type="text" name="${fieldName}" class="regular-text">`;
                        break;
                }
                fieldHtml += '</div>';
                $rowContent.append(fieldHtml);
            });

            $rows.append($newRow);
            $rowContent.find('.acf-pro-date-picker').datepicker({
                dateFormat: $rowContent.find('.acf-pro-date-picker').data('format')
            });
        });

        $fc.on('click', '.acf-pro-fc-remove-row', function() {
            if (confirm(acfProL10n.confirmRemove)) {
                $(this).closest('.acf-pro-fc-row').remove();
            }
        });
    });
});