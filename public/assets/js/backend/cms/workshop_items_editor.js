define(['jquery', 'backend/cms/media_preview'], function ($, MediaPreview) {
    var EDITOR_SELECTOR = '[data-workshop-items-editor]';
    var ITEM_SELECTOR = '[data-workshop-item]';
    var draggedItem = null;

    var reindex = function (editor) {
        editor = $(editor);
        var prefix = String(editor.attr('data-input-prefix') || '');
        var items = editor.find('[data-workshop-items-list]').children(ITEM_SELECTOR);
        var count = items.length;
        items.each(function (index) {
            var item = $(this);
            item.find('[data-workshop-item-label]').first().text('生产车间项目 #' + (index + 1));
            item.find('[data-workshop-field]').each(function () {
                var input = $(this);
                var field = String(input.attr('data-workshop-field') || '');
                if (field) {
                    input.attr('name', prefix + '[' + index + '][' + field + ']');
                }
            });
            item.find('[data-workshop-item-up]').prop('disabled', index === 0);
            item.find('[data-workshop-item-down]').prop('disabled', index === count - 1);
        });
        return editor;
    };

    var add = function (editor, form, Form) {
        editor = $(editor);
        var markup = editor.find('[data-workshop-item-template]').first().html() || '';
        var item = $('<div></div>').html(markup).children(ITEM_SELECTOR).first();
        if (!item.length) {
            return;
        }
        editor.find('[data-workshop-items-list]').first().append(item);
        reindex(editor);
        MediaPreview.refresh(form, Form);
    };

    var bind = function (form, Form) {
        form = $(form);
        form.off('.cmsWorkshopItemsEditor');

        form.on('click.cmsWorkshopItemsEditor', '[data-workshop-item-add]', function () {
            add($(this).closest(EDITOR_SELECTOR), form, Form);
        });
        form.on('click.cmsWorkshopItemsEditor', '[data-workshop-item-remove]', function () {
            var item = $(this).closest(ITEM_SELECTOR), editor = item.closest(EDITOR_SELECTOR);
            item.remove();
            reindex(editor);
        });
        form.on('click.cmsWorkshopItemsEditor', '[data-workshop-item-up]', function () {
            var item = $(this).closest(ITEM_SELECTOR), previous = item.prev(ITEM_SELECTOR);
            if (previous.length) {
                item.insertBefore(previous);
                reindex(item.closest(EDITOR_SELECTOR));
            }
        });
        form.on('click.cmsWorkshopItemsEditor', '[data-workshop-item-down]', function () {
            var item = $(this).closest(ITEM_SELECTOR), next = item.next(ITEM_SELECTOR);
            if (next.length) {
                item.insertAfter(next);
                reindex(item.closest(EDITOR_SELECTOR));
            }
        });
        form.on('mousedown.cmsWorkshopItemsEditor', '[data-workshop-drag-handle]', function () {
            $(this).closest(ITEM_SELECTOR).attr('draggable', 'true');
        });
        form.on('dragstart.cmsWorkshopItemsEditor', ITEM_SELECTOR, function (event) {
            draggedItem = $(this);
            draggedItem.addClass('is-dragging');
            var original = event.originalEvent;
            if (original && original.dataTransfer) {
                original.dataTransfer.effectAllowed = 'move';
                original.dataTransfer.setData('text/plain', 'workshop-item');
            }
        });
        form.on('dragover.cmsWorkshopItemsEditor', ITEM_SELECTOR, function (event) {
            if (!draggedItem || draggedItem[0] === this) {
                return;
            }
            event.preventDefault();
            if (event.originalEvent && event.originalEvent.dataTransfer) {
                event.originalEvent.dataTransfer.dropEffect = 'move';
            }
        });
        form.on('drop.cmsWorkshopItemsEditor', ITEM_SELECTOR, function (event) {
            if (!draggedItem || draggedItem[0] === this) {
                return;
            }
            event.preventDefault();
            var target = $(this);
            var original = event.originalEvent || event;
            var midpoint = target.offset().top + target.outerHeight() / 2;
            if (typeof original.pageY !== 'undefined' && original.pageY > midpoint) {
                draggedItem.insertAfter(target);
            } else {
                draggedItem.insertBefore(target);
            }
            reindex(target.closest(EDITOR_SELECTOR));
        });
        form.on('dragend.cmsWorkshopItemsEditor', ITEM_SELECTOR, function () {
            $(this).attr('draggable', 'false').removeClass('is-dragging');
            draggedItem = null;
        });
        form.on('mouseup.cmsWorkshopItemsEditor', '[data-workshop-drag-handle]', function () {
            var item = $(this).closest(ITEM_SELECTOR);
            window.setTimeout(function () {
                if (!item.hasClass('is-dragging')) {
                    item.attr('draggable', 'false');
                }
            }, 0);
        });

        form.find(EDITOR_SELECTOR).each(function () { reindex(this); });
        return form;
    };

    return { bind: bind, reindex: reindex };
});
