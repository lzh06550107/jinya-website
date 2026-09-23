define(['jquery', 'backend/cms/media_preview'], function ($, MediaPreview) {
    var EDITOR_SELECTOR = '[data-culture-items-editor]';
    var ITEM_SELECTOR = '[data-culture-item]';

    var reindex = function (editor) {
        editor = $(editor);
        var prefix = String(editor.attr('data-input-prefix') || '');
        var items = editor.find('[data-culture-items-list]').children(ITEM_SELECTOR);
        var count = items.length;
        items.each(function (index) {
            var item = $(this);
            item.find('[data-culture-item-label]').first().text('企业文化 #' + (index + 1));
            item.find('[data-culture-field]').each(function () {
                var input = $(this);
                var field = String(input.attr('data-culture-field') || '');
                if (field) {
                    input.attr('name', prefix + '[' + index + '][' + field + ']');
                }
            });
            item.find('[data-culture-item-up]').prop('disabled', index === 0);
            item.find('[data-culture-item-down]').prop('disabled', index === count - 1);
        });
        return editor;
    };

    var add = function (editor, form, Form) {
        editor = $(editor);
        var markup = editor.find('[data-culture-item-template]').first().html() || '';
        var item = $('<div></div>').html(markup).children(ITEM_SELECTOR).first();
        if (!item.length) {
            return;
        }
        editor.find('[data-culture-items-list]').first().append(item);
        reindex(editor);
        MediaPreview.refresh(form, Form);
    };

    var bind = function (form, Form) {
        form = $(form);
        form.off('.cmsCultureItemsEditor');
        form.on('click.cmsCultureItemsEditor', '[data-culture-item-add]', function () {
            add($(this).closest(EDITOR_SELECTOR), form, Form);
        });
        form.on('click.cmsCultureItemsEditor', '[data-culture-item-remove]', function () {
            var item = $(this).closest(ITEM_SELECTOR), editor = item.closest(EDITOR_SELECTOR);
            item.remove();
            reindex(editor);
        });
        form.on('click.cmsCultureItemsEditor', '[data-culture-item-up]', function () {
            var item = $(this).closest(ITEM_SELECTOR), previous = item.prev(ITEM_SELECTOR);
            if (previous.length) {
                item.insertBefore(previous);
                reindex(item.closest(EDITOR_SELECTOR));
            }
        });
        form.on('click.cmsCultureItemsEditor', '[data-culture-item-down]', function () {
            var item = $(this).closest(ITEM_SELECTOR), next = item.next(ITEM_SELECTOR);
            if (next.length) {
                item.insertAfter(next);
                reindex(item.closest(EDITOR_SELECTOR));
            }
        });
        form.find(EDITOR_SELECTOR).each(function () { reindex(this); });
        return form;
    };

    return {bind: bind, reindex: reindex};
});
