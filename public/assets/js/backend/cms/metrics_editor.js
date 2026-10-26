define(['jquery', 'backend/cms/media_preview'], function ($, MediaPreview) {
    var EDITOR_SELECTOR = '[data-metrics-editor]';
    var ITEM_SELECTOR = '[data-metric-item]';

    var reindex = function (editor) {
        editor = $(editor);
        var prefix = String(editor.attr('data-input-prefix') || '');
        var itemLabel = String(editor.attr('data-item-label') || '服务指标');
        var items = editor.find('[data-metrics-list]').children(ITEM_SELECTOR);
        var count = items.length;
        items.each(function (index) {
            var item = $(this);
            item.find('[data-metric-item-label]').first().text(itemLabel + ' #' + (index + 1));
            item.find('[data-metric-field]').each(function () {
                var input = $(this);
                var field = String(input.attr('data-metric-field') || '');
                if (field) {
                    input.attr('name', prefix + '[' + index + '][' + field + ']');
                }
            });
            item.find('[data-metric-item-up]').prop('disabled', index === 0);
            item.find('[data-metric-item-down]').prop('disabled', index === count - 1);
        });
        return editor;
    };

    var add = function (editor, form, Form) {
        editor = $(editor);
        var markup = editor.find('[data-metric-item-template]').first().html() || '';
        var item = $('<div></div>').html(markup).children(ITEM_SELECTOR).first();
        if (!item.length) { return; }
        editor.find('[data-metrics-list]').first().append(item);
        reindex(editor);
        MediaPreview.refresh(form, Form);
    };

    var bind = function (form, Form) {
        form = $(form);
        form.off('.cmsMetricsEditor');
        form.on('click.cmsMetricsEditor', '[data-metric-item-add]', function () {
            add($(this).closest(EDITOR_SELECTOR), form, Form);
        });
        form.on('click.cmsMetricsEditor', '[data-metric-item-remove]', function () {
            var item = $(this).closest(ITEM_SELECTOR), editor = item.closest(EDITOR_SELECTOR);
            item.remove(); reindex(editor);
        });
        form.on('click.cmsMetricsEditor', '[data-metric-item-up]', function () {
            var item = $(this).closest(ITEM_SELECTOR), previous = item.prev(ITEM_SELECTOR);
            if (previous.length) { item.insertBefore(previous); reindex(item.closest(EDITOR_SELECTOR)); }
        });
        form.on('click.cmsMetricsEditor', '[data-metric-item-down]', function () {
            var item = $(this).closest(ITEM_SELECTOR), next = item.next(ITEM_SELECTOR);
            if (next.length) { item.insertAfter(next); reindex(item.closest(EDITOR_SELECTOR)); }
        });
        form.on('click.cmsMetricsEditor', '[data-metric-unit-option]', function (event) {
            event.preventDefault();
            var option = $(this);
            var combobox = option.closest('[data-metric-unit-combobox]');
            var input = combobox.find('[data-metric-field="unit"]').first();
            if (!input.length) { return; }
            input.val(String(option.attr('data-metric-unit-option') || option.text() || ''))
                .trigger('input')
                .trigger('change')
                .focus();
        });
        form.find(EDITOR_SELECTOR).each(function () { reindex(this); });
        return form;
    };

    return { bind: bind, reindex: reindex };
});
