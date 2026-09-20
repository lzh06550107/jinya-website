define(['jquery'], function ($) {
    var EDITOR_SELECTOR = '[data-banner-highlights-editor]';
    var ITEM_SELECTOR = '[data-banner-highlight-item]';
    var MAX_ITEMS = 5;

    var visible = function (value) {
        return value === true || value === 1 || value === '1' || value === 'true' || value === 'yes' || value === 'on';
    };

    var parse = function (value) {
        var items = [];
        try {
            items = JSON.parse($.trim(value || '') || '[]');
        } catch (e) {
            items = [];
        }
        if (!$.isArray(items)) {
            return [];
        }
        return $.map(items.slice(0, MAX_ITEMS), function (item) {
            item = item && typeof item === 'object' ? item : {};
            return {
                icon: $.trim(String(item.icon || '')),
                text: $.trim(String(item.text || '')),
                pc_visible: visible(item.pc_visible) ? 1 : 0,
                mobile_visible: visible(item.mobile_visible) ? 1 : 0
            };
        });
    };

    var itemMarkup = function (item) {
        var panel = $('<div class="panel panel-default cms-banner-highlight-item" data-banner-highlight-item></div>');
        var heading = $('<div class="panel-heading clearfix"></div>');
        heading.append('<strong data-banner-highlight-label>卖点</strong>');
        heading.append(
            '<div class="btn-group btn-group-xs pull-right">' +
            '<button type="button" class="btn btn-default" data-banner-highlight-up title="上移"><i class="fa fa-arrow-up"></i></button>' +
            '<button type="button" class="btn btn-default" data-banner-highlight-down title="下移"><i class="fa fa-arrow-down"></i></button>' +
            '<button type="button" class="btn btn-danger" data-banner-highlight-remove title="删除"><i class="fa fa-trash"></i></button>' +
            '</div>'
        );
        var body = $('<div class="panel-body"></div>');
        var iconGroup = $('<div class="form-group"></div>');
        iconGroup.append('<label class="control-label col-xs-12 col-sm-2">图标:</label>');
        var iconCol = $('<div class="col-xs-12 col-sm-10"></div>');
        var icon = $('<input type="text" class="form-control" data-banner-highlight-field="icon" data-cms-icon-picker="true" data-media-usage="Banner 卖点图标" data-media-recommended-width="64" data-media-recommended-height="64" data-media-recommended-format="PNG/WebP" data-media-max-kb="100" data-media-transparent="true" placeholder="优先从图标库选择；没有合适图标时再上传图片图标">');
        icon.val(item.icon || '');
        iconCol.append(icon);
        iconGroup.append(iconCol);
        body.append(iconGroup);

        var textGroup = $('<div class="form-group"></div>');
        textGroup.append('<label class="control-label col-xs-12 col-sm-2">文案:</label>');
        var textCol = $('<div class="col-xs-12 col-sm-10"></div>');
        var text = $('<input type="text" class="form-control" maxlength="100" data-banner-highlight-field="text" placeholder="例如：专业团队 美院人才">');
        text.val(item.text || '');
        textCol.append(text);
        textGroup.append(textCol);
        body.append(textGroup);

        var visibilityGroup = $('<div class="form-group"></div>');
        visibilityGroup.append('<label class="control-label col-xs-12 col-sm-2">终端显示:</label>');
        var visibilityCol = $('<div class="col-xs-12 col-sm-10"></div>');
        var pcLabel = $('<label class="checkbox-inline"><input type="checkbox" value="1" data-banner-highlight-field="pc_visible"> PC 显示</label>');
        var mobileLabel = $('<label class="checkbox-inline"><input type="checkbox" value="1" data-banner-highlight-field="mobile_visible"> Mobile 显示</label>');
        pcLabel.find('input').prop('checked', !!item.pc_visible);
        mobileLabel.find('input').prop('checked', !!item.mobile_visible);
        visibilityCol.append(pcLabel).append(mobileLabel);
        visibilityGroup.append(visibilityCol);
        body.append(visibilityGroup);

        panel.append(heading).append(body);
        return panel;
    };

    var readItems = function (editor) {
        var items = [];
        $(editor).find('[data-banner-highlights-list]').children(ITEM_SELECTOR).each(function () {
            var item = $(this);
            items.push({
                icon: $.trim(item.find('[data-banner-highlight-field="icon"]').val() || ''),
                text: $.trim(item.find('[data-banner-highlight-field="text"]').val() || ''),
                pc_visible: item.find('[data-banner-highlight-field="pc_visible"]').prop('checked') ? 1 : 0,
                mobile_visible: item.find('[data-banner-highlight-field="mobile_visible"]').prop('checked') ? 1 : 0
            });
        });
        return items.slice(0, MAX_ITEMS);
    };

    var sync = function (editor) {
        editor = $(editor);
        editor.find('[data-banner-highlights-input]').first().val(JSON.stringify(readItems(editor)));
    };

    var updateControls = function (editor) {
        editor = $(editor);
        var items = editor.find('[data-banner-highlights-list]').children(ITEM_SELECTOR);
        var count = items.length;
        items.each(function (index) {
            var item = $(this);
            item.find('[data-banner-highlight-label]').first().text('卖点 #' + (index + 1));
            item.find('[data-banner-highlight-up]').prop('disabled', index === 0);
            item.find('[data-banner-highlight-down]').prop('disabled', index === count - 1);
        });
        editor.find('[data-banner-highlight-add]').prop('disabled', count >= MAX_ITEMS);
        editor.find('[data-banner-highlight-count]').text(count + ' / ' + MAX_ITEMS);
    };

    var render = function (editor) {
        editor = $(editor);
        var input = editor.find('[data-banner-highlights-input]').first();
        var list = editor.find('[data-banner-highlights-list]').first();
        var items = parse(input.val());
        list.empty();
        $.each(items, function (_, item) {
            list.append(itemMarkup(item));
        });
        updateControls(editor);
        sync(editor);
    };

    var bind = function (form, Form) {
        form = $(form);
        form.off('.cmsBannerHighlightEditor');

        form.on('click.cmsBannerHighlightEditor', '[data-banner-highlight-add]', function () {
            var editor = $(this).closest(EDITOR_SELECTOR);
            var list = editor.find('[data-banner-highlights-list]').first();
            if (list.children(ITEM_SELECTOR).length >= MAX_ITEMS) {
                return;
            }
            list.append(itemMarkup({icon: '', text: '', pc_visible: 1, mobile_visible: 1}));
            updateControls(editor);
            sync(editor);
        });

        form.on('click.cmsBannerHighlightEditor', '[data-banner-highlight-remove]', function () {
            var item = $(this).closest(ITEM_SELECTOR);
            var editor = item.closest(EDITOR_SELECTOR);
            item.remove();
            updateControls(editor);
            sync(editor);
        });

        form.on('click.cmsBannerHighlightEditor', '[data-banner-highlight-up]', function () {
            var item = $(this).closest(ITEM_SELECTOR);
            var previous = item.prev(ITEM_SELECTOR);
            if (previous.length) {
                item.insertBefore(previous);
                var editor = item.closest(EDITOR_SELECTOR);
                updateControls(editor);
                sync(editor);
            }
        });

        form.on('click.cmsBannerHighlightEditor', '[data-banner-highlight-down]', function () {
            var item = $(this).closest(ITEM_SELECTOR);
            var next = item.next(ITEM_SELECTOR);
            if (next.length) {
                item.insertAfter(next);
                var editor = item.closest(EDITOR_SELECTOR);
                updateControls(editor);
                sync(editor);
            }
        });

        form.on('input.cmsBannerHighlightEditor change.cmsBannerHighlightEditor', '[data-banner-highlight-field]', function () {
            sync($(this).closest(EDITOR_SELECTOR));
        });

        form.find(EDITOR_SELECTOR).each(function () {
            render(this);
        });
        return form;
    };

    return {
        bind: bind,
        render: render,
        sync: sync,
        maxItems: MAX_ITEMS
    };
});
