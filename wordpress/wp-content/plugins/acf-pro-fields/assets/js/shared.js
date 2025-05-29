jQuery(function($) {
    window.acfPro = window.acfPro || {};

    acfPro.initMediaFrame = function(options) {
        return wp.media({
            title: options.title || acfProL10n.selectFile,
            button: { text: options.buttonText || acfProL10n.selectFile },
            multiple: options.multiple || false,
        });
    };
});