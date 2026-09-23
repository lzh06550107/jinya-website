define(['jquery', 'backend/cms/media_preview'], function ($, MediaPreview) {
    var EDITOR_SELECTOR = '[data-media-items-editor]';
    var ITEM_SELECTOR = '[data-media-item]';

    var reindex = function (editor) {
        editor = $(editor);
        var prefix = String(editor.attr('data-input-prefix') || '');
        var items = editor.find('[data-media-items-list]').children(ITEM_SELECTOR);
        var count = items.length;

        items.each(function (index) {
            var item = $(this);
            item.find('[data-media-item-label]').first().text('媒体项目 #' + (index + 1));
            item.find('[data-media-field]').each(function () {
                var input = $(this);
                var field = String(input.attr('data-media-field') || '');
                if (field) {
                    input.attr('name', prefix + '[' + index + '][' + field + ']');
                }
            });
            item.find('[data-media-item-up]').prop('disabled', index === 0);
            item.find('[data-media-item-down]').prop('disabled', index === count - 1);
        });
        return editor;
    };

    var add = function (editor, form, Form) {
        editor = $(editor);
        var template = editor.find('[data-media-item-template]').first();
        var markup = template.html() || '';
        var holder = $('<div></div>').html(markup);
        var item = holder.children(ITEM_SELECTOR).first();
        if (!item.length) {
            return;
        }
        editor.find('[data-media-items-list]').first().append(item);
        reindex(editor);
        MediaPreview.refresh(form, Form);
    };

    var remove = function (item) {
        item = $(item);
        var editor = item.closest(EDITOR_SELECTOR);
        item.remove();
        reindex(editor);
    };

    var moveUp = function (item) {
        item = $(item);
        var previous = item.prev(ITEM_SELECTOR);
        if (!previous.length) {
            return;
        }
        item.insertBefore(previous);
        reindex(item.closest(EDITOR_SELECTOR));
    };

    var moveDown = function (item) {
        item = $(item);
        var next = item.next(ITEM_SELECTOR);
        if (!next.length) {
            return;
        }
        item.insertAfter(next);
        reindex(item.closest(EDITOR_SELECTOR));
    };

    var bind = function (form, Form) {
        form = $(form);
        form.off('.cmsMediaItemsEditor');
        form.on('click.cmsMediaItemsEditor', '[data-media-item-add]', function () {
            var editor = $(this).closest(EDITOR_SELECTOR);
            add(editor, form, Form);
        });
        form.on('click.cmsMediaItemsEditor', '[data-media-item-remove]', function () {
            remove($(this).closest(ITEM_SELECTOR));
        });
        form.on('click.cmsMediaItemsEditor', '[data-media-item-up]', function () {
            moveUp($(this).closest(ITEM_SELECTOR));
        });
        form.on('click.cmsMediaItemsEditor', '[data-media-item-down]', function () {
            moveDown($(this).closest(ITEM_SELECTOR));
        });
        form.find(EDITOR_SELECTOR).each(function () {
            reindex(this);
        });
        return form;
    };

    return {
        bind: bind,
        reindex: reindex
    };
});
